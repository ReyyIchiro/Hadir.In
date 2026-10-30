<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Enums\MembershipRole;
use App\Enums\ReviewStatus;
use App\Enums\TenantStatus;
use App\Models\AttendanceDay;
use App\Models\AttendanceEvent;
use App\Models\AttendanceReview;
use App\Models\Division;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\Location;
use App\Models\Membership;
use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            $this->command?->warn('Seeder demo dilewati di lingkungan '.app()->environment().'.');

            return;
        }

        // Re-running the seeder should replace the demo data instead of failing on
        // the unique slug. Deleting the tenant cascades to every tenant-scoped row.
        $existing = Tenant::where('slug', 'hadir-in-demo')->first();

        if ($existing) {
            $existing->delete();
        }

        User::whereIn('email', [
            'pemilik@hadir.test',
            'admin@hadir.test',
            'peninjau@hadir.test',
            'anggota@hadir.test',
            'platform@hadir.test',
        ])->delete();

        DB::transaction(function () {
            $tenant = Tenant::create([
                'name' => 'Hadir.In Demo',
                'slug' => 'hadir-in-demo',
                'timezone' => 'Asia/Jakarta',
                'status' => TenantStatus::Active,
                'plan' => 'standard',
                'trial_ends_at' => null,
            ]);

            TenantSetting::create([
                'tenant_id' => $tenant->id,
                'work_start' => '08:00:00',
                'work_end' => '17:00:00',
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
                'early_window_minutes' => 60,
                'grace_minutes' => 10,
                'checkout_late_minutes' => 240,
                'early_leave_minutes' => 10,
                'allow_unscheduled_attendance' => false,
                'require_location' => true,
                'require_selfie' => true,
                'geofence_buffer_meters' => 50,
                'low_accuracy_meters' => 100,
                'gps_timeout_seconds' => 15,
                'selfie_retention_days' => 90,
            ]);

            $office = Location::create([
                'tenant_id' => $tenant->id,
                'name' => 'Kantor Pusat',
                'address' => 'Jalan Jenderal Sudirman 52, Jakarta Selatan',
                'latitude' => -6.2247167,
                'longitude' => 106.8085123,
                'radius_meters' => 60,
                'is_active' => true,
            ]);

            $warehouse = Location::create([
                'tenant_id' => $tenant->id,
                'name' => 'Gudang Selatan',
                'address' => 'Jalan Raya Lenteng Agung 12, Jakarta Selatan',
                'latitude' => -6.3082194,
                'longitude' => 106.8378411,
                'radius_meters' => 120,
                'is_active' => true,
            ]);

            $operations = Division::create([
                'tenant_id' => $tenant->id,
                'name' => 'Operasional',
                'description' => 'Produksi, gudang, dan pengiriman.',
                'is_active' => true,
            ]);

            $finance = Division::create([
                'tenant_id' => $tenant->id,
                'name' => 'Keuangan',
                'description' => 'Akuntansi dan administrasi.',
                'is_active' => true,
            ]);

            $owner = $this->user('Pemilik Demo', 'pemilik@hadir.test');
            $admin = $this->user('Admin Demo', 'admin@hadir.test');
            $reviewer = $this->user('Peninjau Demo', 'peninjau@hadir.test');
            $member = $this->user('Anggota Demo', 'anggota@hadir.test');

            $this->user('Admin Platform', 'platform@hadir.test', platformAdmin: true);

            $this->membership($tenant, $owner, MembershipRole::Owner, $office, $finance);
            $this->membership($tenant, $admin, MembershipRole::Admin, $office, $finance);
            $this->membership($tenant, $reviewer, MembershipRole::Reviewer, $office, $operations);
            $this->membership($tenant, $member, MembershipRole::Member, $office, $operations);

            $today = CarbonImmutable::now($tenant->timezone)->startOfDay();

            // Jadwal dibuat mundur tiga minggu plus sisa bulan berjalan dan satu
            // bulan ke depan. Hari sebelum aplikasi dipakai tidak diberi jadwal
            // supaya laporan tidak menampilkan absen palsu.
            $this->schedules($tenant, [$owner, $admin, $reviewer, $member], $today);

            Holiday::create([
                'tenant_id' => $tenant->id,
                'date' => $today->addDays(14)->toDateString(),
                'name' => 'Cuti bersama perusahaan',
            ]);

            $memberAbsences = [];

            foreach ([$owner, $admin, $reviewer, $member] as $index => $person) {
                $absences = $this->attendanceHistory($tenant, $person, $office, $today, $index * 2);

                if ($person->is($member)) {
                    $memberAbsences = $absences;
                }
            }

            $this->rejectedSample($tenant, $member, $today, $memberAbsences);

            $this->leaveSamples($tenant, $member, $reviewer, $today);
        });
    }

    protected function user(string $name, string $email, bool $platformAdmin = false): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'),
            'is_platform_admin' => $platformAdmin,
        ]);
    }

    protected function membership(Tenant $tenant, User $user, MembershipRole $role, Location $location, Division $division): Membership
    {
        return Membership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role' => $role,
            'primary_location_id' => $location->id,
            'division_id' => $division->id,
            'is_active' => true,
            'joined_at' => now(),
        ]);
    }

    /**
     * @param  array<int, User>  $users
     */
    protected function schedules(Tenant $tenant, array $users, CarbonImmutable $today): void
    {
        // Tiga minggu ke belakang supaya laporan bulan berjalan punya isi, lalu
        // sisa bulan ini dan satu bulan penuh ke depan supaya pengajuan cuti
        // tetap bisa divalidasi terhadap jadwal kerja.
        $start = $today->subWeeks(3)->startOfWeek();
        $end = $today->startOfMonth()->addMonthNoOverflow()->endOfMonth();

        foreach ($users as $user) {
            for ($date = $start; $date->lte($end); $date = $date->addDay()) {
                if ($date->isWeekend()) {
                    continue;
                }

                Schedule::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'work_date' => $date->toDateString(),
                    'is_day_off' => false,
                    'start_time' => '08:00:00',
                    'end_time' => '17:00:00',
                    'break_start' => '12:00:00',
                    'break_end' => '13:00:00',
                ]);
            }
        }
    }

    /**
     * Riwayat presensi untuk satu anggota pada setiap hari kerja tiga minggu
     * terakhir. Pola berulang membuat laporan punya campuran hadir, terlambat,
     * absen, dan satu hari tanpa absen pulang.
     */
    protected function attendanceHistory(Tenant $tenant, User $user, Location $office, CarbonImmutable $today, int $offset = 0): array
    {
        $patterns = [
            ['07.52', '17.04', AttendanceStatus::Hadir, 0],
            ['08.06', '17.12', AttendanceStatus::Hadir, 0],
            ['08.23', '16.59', AttendanceStatus::Terlambat, 23],
            ['07.48', '17.21', AttendanceStatus::Hadir, 0],
            ['08.01', '17.02', AttendanceStatus::Hadir, 0],
            ['08.11', '17.07', AttendanceStatus::Terlambat, 11],
            [null, null, AttendanceStatus::Absen, 0],
            ['07.57', '17.00', AttendanceStatus::Hadir, 0],
            ['08.04', '17.15', AttendanceStatus::Hadir, 0],
        ];

        $cursor = $offset;
        $start = $today->subWeeks(3)->startOfWeek();
        $absentDates = [];

        for ($date = $start; $date->lt($today); $date = $date->addDay()) {
            if ($date->isWeekend()) {
                continue;
            }

            [$in, $out, $status, $late] = $patterns[$cursor % count($patterns)];
            $cursor++;

            $checkIn = $in ? $this->localTimestamp($date, $in) : null;
            $checkOut = $out ? $this->localTimestamp($date, $out) : null;

            $flags = match (true) {
                $status === AttendanceStatus::Absen => ['final_otomatis'],
                $checkIn !== null && $checkOut === null => ['final_otomatis', 'tanpa_pulang'],
                default => [],
            };

            $day = AttendanceDay::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'work_date' => $date->toDateString(),
                'location_id' => $status === AttendanceStatus::Absen ? null : $office->id,
                'status' => $status,
                'check_in_at' => $checkIn,
                'check_out_at' => $checkOut,
                'late_minutes' => $late,
                'flags' => $flags,
                'finalized_at' => $this->localTimestamp($date, '21.00'),
            ]);

            if ($checkIn === null) {
                $absentDates[] = $date;

                continue;
            }

            AttendanceEvent::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'attendance_day_id' => $day->id,
                'type' => 'check_in',
                'status' => 'accepted',
                'work_date' => $date->toDateString(),
                'occurred_at' => $checkIn,
                'location_id' => $office->id,
                'latitude' => $office->latitude,
                'longitude' => $office->longitude,
                'accuracy_meters' => 9,
                'distance_meters' => 14,
                'flags' => [],
                'attempt_uuid' => (string) Str::uuid(),
            ]);

            if ($checkOut !== null) {
                AttendanceEvent::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'attendance_day_id' => $day->id,
                    'type' => 'check_out',
                    'status' => 'accepted',
                    'work_date' => $date->toDateString(),
                    'occurred_at' => $checkOut,
                    'location_id' => $office->id,
                    'latitude' => $office->latitude,
                    'longitude' => $office->longitude,
                    'accuracy_meters' => 11,
                    'distance_meters' => 18,
                    'flags' => [],
                    'attempt_uuid' => (string) Str::uuid(),
                ]);
            }
        }

        return $absentDates;
    }

    /**
     * Satu percobaan absen yang ditolak karena di luar radius, plus pengajuan
     * tinjauan yang masih menunggu keputusan. Ditempel ke hari yang tercatat
     * absen supaya ceritanya utuh: absen ditolak, hari difinalisasi absen,
     * anggota meminta peninjauan.
     *
     * @param  array<int, CarbonImmutable>  $absentDates
     */
    protected function rejectedSample(Tenant $tenant, User $member, CarbonImmutable $today, array $absentDates): void
    {
        $rejectedDate = $absentDates ? end($absentDates) : $today->subDays(4);
        $rejectedAt = $this->localTimestamp($rejectedDate, '08.02');

        $rejected = AttendanceEvent::create([
            'tenant_id' => $tenant->id,
            'user_id' => $member->id,
            'type' => 'check_in',
            'status' => 'rejected',
            'work_date' => $rejectedDate->toDateString(),
            'occurred_at' => $rejectedAt,
            'location_id' => null,
            'latitude' => -6.2291000,
            'longitude' => 106.8150000,
            'accuracy_meters' => 24,
            'distance_meters' => 812,
            'reason' => 'di_luar_radius',
            'flags' => [],
            'attempt_uuid' => (string) Str::uuid(),
        ]);

        AttendanceReview::create([
            'tenant_id' => $tenant->id,
            'attendance_event_id' => $rejected->id,
            'user_id' => $member->id,
            'status' => ReviewStatus::Pending,
            'note' => 'Saya berada di lobi kantor, GPS mungkin meleset.',
        ]);
    }

    protected function leaveSamples(Tenant $tenant, User $member, User $reviewer, CarbonImmutable $today): void
    {
        LeaveRequest::create([
            'tenant_id' => $tenant->id,
            'user_id' => $member->id,
            'type' => LeaveType::Izin,
            'start_date' => $today->addDays(6)->toDateString(),
            'end_date' => $today->addDays(6)->toDateString(),
            'reason' => 'Urusan administrasi keluarga.',
            'status' => LeaveStatus::Pending,
        ]);

        $sickDate = $today->subDays(8);

        while ($sickDate->isWeekend()) {
            $sickDate = $sickDate->subDay();
        }

        $approved = LeaveRequest::create([
            'tenant_id' => $tenant->id,
            'user_id' => $member->id,
            'type' => LeaveType::Sakit,
            'start_date' => $sickDate->toDateString(),
            'end_date' => $sickDate->toDateString(),
            'reason' => 'Demam, surat dokter menyusul.',
            'status' => LeaveStatus::Approved,
            'reviewed_by_id' => $reviewer->id,
            'reviewed_at' => $this->localTimestamp($sickDate->subDay(), '10.00'),
            'created_at' => $this->localTimestamp($sickDate->subDays(2), '08.30'),
            'updated_at' => $this->localTimestamp($sickDate->subDay(), '10.00'),
        ]);

        // Persetujuan izin membuat hari presensi bertipe izin. Seeder menulis
        // langsung supaya tidak bergantung pada layanan dan pengguna peninjau.
        AttendanceDay::updateOrCreate(
            ['user_id' => $member->id, 'work_date' => $sickDate->toDateString()],
            [
                'tenant_id' => $tenant->id,
                'leave_request_id' => $approved->id,
                'status' => AttendanceStatus::Sakit,
                'late_minutes' => 0,
                'flags' => [],
                'finalized_at' => $this->localTimestamp($sickDate, '21.00'),
            ],
        );
    }

    protected function localTimestamp(CarbonImmutable $date, string $time): CarbonImmutable
    {
        $normalized = str_replace('.', ':', $time);

        return CarbonImmutable::createFromFormat(
            'Y-m-d H:i',
            $date->toDateString().' '.$normalized,
            'Asia/Jakarta',
        )->setTimezone('UTC');
    }
}
