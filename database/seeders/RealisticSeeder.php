<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\CorrectionStatus;
use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Enums\MembershipRole;
use App\Enums\TenantStatus;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceDay;
use App\Models\AttendanceEvent;
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

/**
 * Data besar untuk uji laporan: 60 karyawan, 6 divisi, 5 lokasi, dan empat
 * bulan riwayat presensi dengan pola yang menyerang pengguna nyata, bukan
 * deretan seragam. Masih lingkungan lokal dan testing saja.
 */
class RealisticSeeder extends Seeder
{
    /** Jumlah karyawan, termasuk pemilik, admin, dan peninjau. */
    protected const EMPLOYEE_COUNT = 60;

    /** Bulan riwayat yang diisi, termasuk bulan berjalan. */
    protected const MONTHS = 4;

    /** @var array<string, array<int, string>> */
    protected array $names = [];

    /** @var array<int, array<string, mixed>> */
    protected array $people = [];

    protected Tenant $tenant;

    /** @var array<int, Location> */
    protected array $locations = [];

    /** @var array<int, Division> */
    protected array $divisions = [];

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            $this->command?->warn('Seeder realistis dilewati di lingkungan '.app()->environment().'.');

            return;
        }

        $existing = Tenant::where('slug', 'hadir-in-realistic')->first();

        if ($existing) {
            $existing->delete();
        }

        $startTime = microtime(true);

        DB::transaction(function () {
            $this->buildTenant();
            $this->buildPeople();
            $this->buildHistory();
        });

        $seconds = round(microtime(true) - $startTime, 1);

        $this->command?->info("Seeder realistis selesai dalam {$seconds} detik.");
    }

    protected function buildTenant(): void
    {
        $this->tenant = Tenant::create([
            'name' => 'Hadir.In Realistic',
            'slug' => 'hadir-in-realistic',
            'timezone' => 'Asia/Jakarta',
            'status' => TenantStatus::Active,
            'plan' => 'standard',
            'trial_ends_at' => null,
        ]);

        TenantSetting::create([
            'tenant_id' => $this->tenant->id,
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

        $locations = [
            ['Kantor Pusat', 'Jalan Jenderal Sudirman 52, Jakarta Selatan', -6.2247167, 106.8085123, 60],
            ['Gudang Selatan', 'Jalan Raya Lenteng Agung 12, Jakarta Selatan', -6.3082194, 106.8378411, 120],
            ['Cabang Timur', 'Jalan Darmo Permai 8, Surabaya', -7.2819705, 112.6537941, 80],
            ['Cabang Bandung', 'Jalan Asia Afrika 111, Bandung', -6.9218278, 107.6070500, 70],
            ['Gudang Utara', 'Jalan Raya Cikarang 45, Bekasi', -6.2914787, 107.1525439, 150],
        ];

        foreach ($locations as [$name, $address, $lat, $lng, $radius]) {
            $this->locations[] = Location::create([
                'tenant_id' => $this->tenant->id,
                'name' => $name,
                'address' => $address,
                'latitude' => $lat,
                'longitude' => $lng,
                'radius_meters' => $radius,
                'is_active' => true,
            ]);
        }

        $divisions = [
            ['Operasional', 'Produksi, gudang, dan pengiriman.'],
            ['Keuangan', 'Akuntansi, kas, dan administrasi.'],
            ['Sumber Daya Manusia', 'Rekrutmen, absensi, dan kesejahteraan.'],
            ['Teknologi Informasi', 'Infrastruktur, aplikasi, dan dukungan.'],
            ['Pemasaran', 'Kampanye, mitra, dan sosial media.'],
            ['Layanan Pelanggan', 'Pengaduan, keluhan, dan tiket pelanggan.'],
        ];

        foreach ($divisions as [$name, $description]) {
            $this->divisions[] = Division::create([
                'tenant_id' => $this->tenant->id,
                'name' => $name,
                'description' => $description,
                'is_active' => true,
            ]);
        }

        // Hari libur di dalam rentang data. Tanggal ditetapkan dari tanggal
        // sekarang supaya seeder tetap menghasilkan data yang sama kalau
        // dijalankan beberapa bulan lagi.
        $today = CarbonImmutable::now($this->tenant->timezone)->startOfDay();
        $start = $today->subMonths(self::MONTHS - 1)->startOfMonth();

        foreach ([
            $start->addDays(4)->toDateString() => 'Hari libur nasional',
            $start->addMonths(2)->addDays(9)->toDateString() => 'Cuti bersama perusahaan',
        ] as $dateString => $name) {
            $match = CarbonImmutable::parse($dateString, $this->tenant->timezone);

            if ($match->gte($start) && $match->lte($today)) {
                Holiday::create([
                    'tenant_id' => $this->tenant->id,
                    'date' => $match->toDateString(),
                    'name' => $name,
                ]);
            }
        }
    }

    protected function buildPeople(): void
    {
        $firstNames = [
            'Adi', 'Bagus', 'Citra', 'Dewi', 'Eko', 'Fitri', 'Gita', 'Hendra',
            'Indah', 'Joko', 'Kartika', 'Lestari', 'Maya', 'Nanda', 'Oka',
            'Putri', 'Rizki', 'Sari', 'Taufik', 'Umar', 'Vina', 'Wahyu',
            'Yanti', 'Zaki', 'Bima', 'Candra', 'Dian', 'Fajar', 'Galih', 'Hana',
        ];

        $lastNames = [
            'Santoso', 'Wijaya', 'Pratama', 'Nugroho', 'Kusuma', 'Saputra',
            'Ramadhan', 'Hidayat', 'Maulana', 'Puspita', 'Anggraini', 'Utami',
            'Firmansyah', 'Setiawan', 'Halim', 'Lubis', 'Simanjuntak', 'Sitorus',
        ];

        $roles = [
            MembershipRole::Owner,
            MembershipRole::Admin,
            MembershipRole::Reviewer,
        ];

        $password = Hash::make('password123');
        $today = CarbonImmutable::now($this->tenant->timezone)->startOfDay();

        for ($index = 0; $index < self::EMPLOYEE_COUNT; $index++) {
            $role = $index < count($roles)
                ? $roles[$index]
                : MembershipRole::Member;

            $first = $firstNames[$index % count($firstNames)];
            $last = $lastNames[intdiv($index, count($firstNames)) % count($lastNames)];
            $name = trim($first.' '.$last);
            $email = $index === 0
                ? 'pemilik@hadir.test'
                : ($index === 1
                    ? 'admin@hadir.test'
                    : ($index === 2
                        ? 'peninjau@hadir.test'
                        : 'karyawan'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT).'@hadir.test'));

            if ($index === 0) {
                // Akun demo inti tetap ada supaya tabel akun di README tetap benar.
                $name = 'Pemilik Demo';
            }

            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => $password],
            );

            $this->people[] = [
                'user' => $user,
                'role' => $role,
                'location' => $this->locations[$index % count($this->locations)],
                'division' => $this->divisions[$index % count($this->divisions)],
                'shift' => $index % 4 === 3 ? 'siang' : 'pagi',
                'joined' => $today->subDays(20 + $index * 3),
            ];
        }

        foreach ($this->people as $person) {
            Membership::create([
                'tenant_id' => $this->tenant->id,
                'user_id' => $person['user']->id,
                'role' => $person['role'],
                'primary_location_id' => $person['location']->id,
                'division_id' => $person['division']->id,
                'is_active' => true,
                'joined_at' => $person['joined'],
            ]);
        }
    }

    protected function buildHistory(): void
    {
        $today = CarbonImmutable::now($this->tenant->timezone)->startOfDay();
        $start = $today->subMonths(self::MONTHS - 1)->startOfMonth();
        $end = $today->startOfMonth()->addMonthNoOverflow()->endOfMonth();

        // Jadwal dan presensi dibuat per orang supaya pola datanya konsisten.
        foreach ($this->people as $index => $person) {
            $this->seedPerson($person, $index, $start, $today, $end);
        }

        $this->seedLeaveAndCorrections($start, $end, $today);
    }

    /**
     * @param  array<string, mixed>  $person
     */
    protected function seedPerson(array $person, int $index, CarbonImmutable $start, CarbonImmutable $end, CarbonImmutable $historyEnd): void
    {
        $user = $person['user'];
        $shift = $person['shift'];
        $startTime = $shift === 'siang' ? '10:00:00' : '08:00:00';
        $endTime = $shift === 'siang' ? '19:00:00' : '17:00:00';
        $schedules = [];

        for ($date = $start->copy(); $date->lte($end); $date = $date->addDay()) {
            if ($date->isWeekend()) {
                continue;
            }

            $schedules[] = Schedule::create([
                'tenant_id' => $this->tenant->id,
                'user_id' => $user->id,
                'work_date' => $date->toDateString(),
                'is_day_off' => false,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
            ]);
        }

        // Presensi hanya untuk hari yang sudah lewat. Angka acak stabil per
        // orang supaya hasilnya bisa direproduksi.
        $seed = crc32($user->email);
        mt_srand($seed);

        $lateChance = 8 + ($index % 5) * 2;
        $absentChance = 2 + ($index % 3);
        $leaveChance = 1 + ($index % 2);

        foreach ($schedules as $schedule) {
            $date = CarbonImmutable::parse($schedule->work_date->toDateString());

            if ($date->gte($historyEnd)) {
                continue;
            }

            if ($this->isHoliday($schedule->work_date->toDateString())) {
                continue;
            }

            $roll = mt_rand(1, 100);

            if ($roll <= $absentChance) {
                $this->seedAbsentDay($schedule);

                continue;
            }

            if ($roll <= $absentChance + $leaveChance) {
                continue;
            }

            $late = mt_rand(1, 100) <= $lateChance;
            $checkoutMiss = mt_rand(1, 100) <= 2;

            $checkIn = $late
                ? $this->localTimestamp($date, $shift === 'siang' ? '10.'.mt_rand(11, 45) : '08.'.mt_rand(11, 45))
                : $this->localTimestamp($date, $shift === 'siang' ? '09.'.mt_rand(40, 59) : '07.'.mt_rand(40, 59));

            $checkOut = $checkoutMiss
                ? null
                : $this->localTimestamp($date, $shift === 'siang' ? '19.'.mt_rand(0, 20) : '17.'.mt_rand(0, 20));

            $status = $late ? AttendanceStatus::Terlambat : AttendanceStatus::Hadir;

            $flags = [];

            if ($checkoutMiss) {
                $flags[] = 'final_otomatis';
                $flags[] = 'tanpa_pulang';
            }

            $day = AttendanceDay::create([
                'tenant_id' => $this->tenant->id,
                'user_id' => $user->id,
                'work_date' => $schedule->work_date->toDateString(),
                'schedule_id' => $schedule->id,
                'location_id' => $person['location']->id,
                'status' => $status,
                'check_in_at' => $checkIn,
                'check_out_at' => $checkOut,
                'late_minutes' => $late ? mt_rand(11, 45) : 0,
                'flags' => $flags,
                'finalized_at' => $this->localTimestamp($date, '21.00'),
            ]);

            $this->seedEvent($day, $user, $person['location'], 'check_in', $checkIn);

            if ($checkOut !== null) {
                $this->seedEvent($day, $user, $person['location'], 'check_out', $checkOut);
            }
        }

        mt_srand();
    }

    protected function seedAbsentDay(Schedule $schedule): void
    {
        AttendanceDay::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $schedule->user_id,
            'work_date' => $schedule->work_date->toDateString(),
            'schedule_id' => $schedule->id,
            'location_id' => null,
            'status' => AttendanceStatus::Absen,
            'late_minutes' => 0,
            'flags' => ['final_otomatis'],
            'finalized_at' => $this->localTimestamp(CarbonImmutable::parse($schedule->work_date->toDateString()), '21.00'),
        ]);
    }

    protected function seedEvent(AttendanceDay $day, User $user, Location $location, string $type, CarbonImmutable $occurredAt): void
    {
        AttendanceEvent::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $user->id,
            'attendance_day_id' => $day->id,
            'type' => $type,
            'status' => 'accepted',
            'work_date' => $day->work_date->toDateString(),
            'occurred_at' => $occurredAt,
            'location_id' => $location->id,
            'latitude' => $location->latitude + (mt_rand(-20, 20) / 100000),
            'longitude' => $location->longitude + (mt_rand(-20, 20) / 100000),
            'accuracy_meters' => mt_rand(5, 40),
            'distance_meters' => mt_rand(3, 45),
            'flags' => [],
            'attempt_uuid' => (string) Str::uuid(),
        ]);
    }

    protected function seedLeaveAndCorrections(CarbonImmutable $start, CarbonImmutable $end, CarbonImmutable $today): void
    {
        // Tiga pengajuan menunggu keputusan untuk antrean tinjauan.
        $pending = array_slice($this->people, 3, 3);

        foreach ($pending as $index => $person) {
            $type = [LeaveType::Izin, LeaveType::Sakit, LeaveType::Cuti][$index];
            $date = $today->addDays(3 + $index * 2);

            LeaveRequest::create([
                'tenant_id' => $this->tenant->id,
                'user_id' => $person['user']->id,
                'type' => $type,
                'start_date' => $date->toDateString(),
                'end_date' => $date->toDateString(),
                'reason' => $type === LeaveType::Sakit
                    ? 'Demam, surat dokter menyusul.'
                    : ($type === LeaveType::Cuti ? 'Cuti tahunan.' : 'Urusan administrasi keluarga.'),
                'status' => LeaveStatus::Pending,
            ]);
        }

        // Satu izin disetujui di tengah bulan berjalan supaya laporan
        // mempunyai baris izin, bukan hanya hadir dan absen.
        $approvedPerson = $this->people[5];
        $approvedDate = $today->subDays(6);

        while ($approvedDate->isWeekend()) {
            $approvedDate = $approvedDate->subDay();
        }

        $reviewer = $this->people[2]['user'];

        $approved = LeaveRequest::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $approvedPerson['user']->id,
            'type' => LeaveType::Sakit,
            'start_date' => $approvedDate->toDateString(),
            'end_date' => $approvedDate->toDateString(),
            'reason' => 'Demam, surat dokter menyusul.',
            'status' => LeaveStatus::Approved,
            'reviewed_by_id' => $reviewer->id,
            'reviewed_at' => $this->localTimestamp($approvedDate->subDay(), '10.00'),
            'created_at' => $this->localTimestamp($approvedDate->subDays(2), '08.30'),
            'updated_at' => $this->localTimestamp($approvedDate->subDay(), '10.00'),
        ]);

        AttendanceDay::updateOrCreate(
            ['user_id' => $approvedPerson['user']->id, 'work_date' => $approvedDate->toDateString()],
            [
                'tenant_id' => $this->tenant->id,
                'leave_request_id' => $approved->id,
                'status' => AttendanceStatus::Sakit,
                'late_minutes' => 0,
                'flags' => [],
                'finalized_at' => $this->localTimestamp($approvedDate, '21.00'),
            ],
        );

        // Koreksi jam pulang yang sudah disetujui, plus satu koreksi menunggu.
        $corrected = $this->people[7];
        $correctedDate = $today->subDays(4);

        while ($correctedDate->isWeekend()) {
            $correctedDate = $correctedDate->subDay();
        }

        $day = AttendanceDay::where('user_id', $corrected['user']->id)
            ->where('work_date', $correctedDate->toDateString())
            ->first();

        if ($day !== null) {
            AttendanceCorrection::create([
                'tenant_id' => $this->tenant->id,
                'attendance_day_id' => $day->id,
                'user_id' => $corrected['user']->id,
                'requested_by_id' => $corrected['user']->id,
                'reviewed_by_id' => $reviewer->id,
                'status' => CorrectionStatus::Approved,
                'original_values' => ['check_out_at' => $day->check_out_at?->format('H:i')],
                'requested_values' => ['check_out_at' => '18.00'],
                'reason' => 'Lupa absen pulang karena rapat di luar kantor.',
                'review_note' => 'Disetujui, sesuai catatan rapat.',
                'reviewed_at' => $this->localTimestamp($correctedDate->addDay(), '09.00'),
            ]);

            $day->update([
                'check_out_at' => $this->localTimestamp($correctedDate, '18.00'),
                'flags' => ['dikoreksi'],
                'is_corrected' => true,
            ]);
        }
    }

    protected function isHoliday(string $date): bool
    {
        return Holiday::withoutGlobalScopes()
            ->where('tenant_id', $this->tenant->id)
            ->where('date', $date)
            ->exists();
    }

    protected function localTimestamp(CarbonImmutable $date, string $time): CarbonImmutable
    {
        $normalized = str_replace('.', ':', $time);
        $parts = explode(':', $normalized);

        if (count($parts) === 2) {
            $normalized = $parts[0].':'.str_pad($parts[1], 2, '0', STR_PAD_LEFT);
        }

        return CarbonImmutable::createFromFormat(
            'Y-m-d H:i',
            $date->toDateString().' '.$normalized,
            'Asia/Jakarta',
        )->setTimezone('UTC');
    }
}
