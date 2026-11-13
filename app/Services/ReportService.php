<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\MembershipRole;
use App\Enums\ReviewStatus;
use App\Models\AttendanceDay;
use App\Models\AttendanceReview;
use App\Models\Holiday;
use App\Models\Membership;
use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Support\Flags;
use App\Support\Waktu;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Generator;

class ReportService
{
    /**
     * Ringkasan dan baris laporan satu bulan. Baris dapat dihaluskan lewat
     * pagination ($page null berarti seluruh baris, dipakai pengujian dan
     * pembacaan langsung), sementara iterasi tetap satu lintasan ringan.
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int, summary: array<string, int>, month: CarbonImmutable}
     */
    public function monthData(
        Tenant $tenant,
        string $month,
        ?int $divisionId = null,
        ?int $locationId = null,
        ?Membership $viewer = null,
        ?int $page = null,
        int $perPage = 25,
    ): array {
        $start = CarbonImmutable::createFromFormat('Y-m', $month, $tenant->timezone)->startOfMonth();
        $materials = $this->monthMaterials($tenant, $month, $divisionId, $locationId, $viewer);

        if ($materials === null) {
            return ['rows' => [], 'total' => 0, 'summary' => $this->emptySummary(), 'month' => $start];
        }

        $summary = $this->emptySummary();
        $rows = [];
        $total = 0;
        $offset = $page === null ? 0 : ($page - 1) * $perPage;

        foreach ($this->walkRows($materials, $summary) as $index => $row) {
            $total++;

            if ($page === null || ($index >= $offset && $index < $offset + $perPage)) {
                $rows[] = $row;
            }
        }

        return ['rows' => $rows, 'total' => $total, 'summary' => $summary, 'month' => $start];
    }

    /**
     * Seluruh baris laporan sebagai generator. Ekspor CSV memakai ini supaya
     * bulan panjang tidak pernah dihidrasi penuh ke memori sekaligus.
     */
    public function monthRows(
        Tenant $tenant,
        string $month,
        ?int $divisionId = null,
        ?int $locationId = null,
        ?Membership $viewer = null,
    ): Generator {
        $materials = $this->monthMaterials($tenant, $month, $divisionId, $locationId, $viewer);

        if ($materials === null) {
            return;
        }

        $none = null;

        yield from $this->walkRows($materials, $none);
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'cuti' => 'Cuti',
            'absen' => 'Absen',
            'belum_absen' => 'Belum absen',
            'belum_pulang' => 'Belum pulang',
            'libur' => 'Libur',
            default => ucfirst($status),
        };
    }

    /**
     * @return Collection<int, Membership>
     */
    public function scopedMemberships(
        Tenant $tenant,
        ?int $divisionId = null,
        ?int $locationId = null,
        ?Membership $viewer = null,
    ): Collection {
        $query = Membership::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->with(['user', 'division', 'primaryLocation']);

        if ($divisionId) {
            $query->where('division_id', $divisionId);
        }

        if ($locationId) {
            $query->where('primary_location_id', $locationId);
        }

        if ($viewer && $viewer->role === MembershipRole::Reviewer && $viewer->primary_location_id) {
            $query->where('primary_location_id', $viewer->primary_location_id);
        }

        return $query->get()
            ->filter(fn (Membership $membership) => $membership->user !== null)
            ->values();
    }

    /**
     * @return array<string, int>
     */
    protected function emptySummary(): array
    {
        return [
            'scheduled' => 0,
            'present' => 0,
            'late' => 0,
            'leave' => 0,
            'absent' => 0,
            'not_finalized' => 0,
            'pending_review' => 0,
        ];
    }

    /**
     * Kumpulan bahan laporan: jadwal terurut, hari presensi berindeks, dan
     * set penunjang. Semua query memilih kolom minimal agar hidrasi tetap
     * murah pada bulan dengan ratusan anggota (ARCHITECTURE 14).
     *
     * @return array<string, mixed>|null
     */
    protected function monthMaterials(
        Tenant $tenant,
        string $month,
        ?int $divisionId,
        ?int $locationId,
        ?Membership $viewer,
    ): ?array {
        $start = CarbonImmutable::createFromFormat('Y-m', $month, $tenant->timezone)->startOfMonth();
        $end = $start->endOfMonth();
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();

        $memberships = $this->scopedMemberships($tenant, $divisionId, $locationId, $viewer);

        if ($memberships->isEmpty()) {
            return null;
        }

        $userIds = $memberships->pluck('user_id')->all();

        $schedules = Schedule::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereIn('user_id', $userIds)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->where('is_day_off', false)
            ->get(['user_id', 'work_date', 'start_time', 'end_time']);

        $holidays = Holiday::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn ($date) => CarbonImmutable::parse($date)->toDateString())
            ->all();

        $days = AttendanceDay::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereIn('user_id', $userIds)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get(['id', 'user_id', 'work_date', 'status', 'check_in_at', 'check_out_at', 'flags', 'note', 'finalized_at'])
            ->keyBy(fn (AttendanceDay $day) => $day->user_id.'|'.$day->work_date->toDateString());

        $reviewUserIds = AttendanceReview::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('status', ReviewStatus::Pending)
            ->whereIn('user_id', $userIds)
            ->whereBetween('created_at', [$start->setTimezone('UTC'), $end->endOfDay()->setTimezone('UTC')])
            ->pluck('user_id')
            ->all();

        // Satu pasang jadwal-anggota per baris. Pengelompokan sekali di sini
        // menggantikan penyaringan per anggota di dalam loop (O(n) total).
        $byUser = $memberships->keyBy('user_id');
        $items = [];

        foreach ($schedules as $schedule) {
            $items[] = [
                'schedule' => $schedule,
                'membership' => $byUser->get($schedule->user_id),
            ];
        }

        usort($items, fn ($a, $b) => [
            $a['schedule']->work_date->toDateString(),
            $a['membership']?->user?->name ?? '',
        ] <=> [
            $b['schedule']->work_date->toDateString(),
            $b['membership']?->user?->name ?? '',
        ]);

        return [
            'items' => $items,
            'days' => $days,
            'holidays' => array_flip($holidays),
            'reviewUserIds' => array_flip($reviewUserIds),
            'today' => Waktu::now($tenant)->toDateString(),
            'settings' => $tenant->settings ?? new TenantSetting(['tenant_id' => $tenant->id]),
            'tenant' => $tenant,
        ];
    }

    /**
     * Satu lintasan atas jadwal bulan berjalan: membangun setiap baris dan,
     * bila $summary diberikan, menghitung ringkasan sekaligus tanpa lintasan
     * kedua.
     *
     * @param array<string, mixed> $materials
     * @param array<string, int>|null $summary
     * @return Generator<int, array<string, mixed>>
     */
    protected function walkRows(array $materials, ?array &$summary): Generator
    {
        $clock = app(TenantClock::class);

        foreach ($materials['items'] as $item) {
            $schedule = $item['schedule'];
            $membership = $item['membership'];

            if ($membership === null) {
                continue;
            }

            $date = $schedule->work_date->toDateString();

            if (isset($materials['holidays'][$date])) {
                continue;
            }

            // Hari yang belum berjalan belum punya hasil apa pun, jadi tidak
            // diikutkan sebagai baris laporan.
            if ($date > $materials['today']) {
                continue;
            }

            $day = $materials['days']->get($membership->user_id.'|'.$date);

            if ($day !== null) {
                $display = $day->displayStatus();
            } else {
                $endTime = $schedule->end_time ?: $materials['settings']->work_end;
                $due = $clock->isFinalizable(
                    $materials['tenant'],
                    $date,
                    $endTime,
                    $materials['settings']->checkout_late_minutes,
                );
                $display = $due ? AttendanceStatus::Absen->value : 'belum_absen';
            }

            $flags = $day?->flags ?? [];

            if ($day === null && isset($materials['reviewUserIds'][$membership->user_id])) {
                $flags[] = Flags::PERLU_DITINJAU;
            }

            $flags = array_values(array_unique($flags));

            $row = [
                'user_id' => $membership->user_id,
                'name' => $membership->user?->name ?? '',
                'email' => $membership->user?->email ?? '',
                'division' => $membership->division?->name ?? '',
                'location' => $membership->primaryLocation?->name ?? '',
                'date' => $date,
                'day_name' => Waktu::dayName($date),
                'schedule' => substr((string) $schedule->start_time, 0, 5).'-'.substr((string) $schedule->end_time, 0, 5),
                'check_in' => $day?->check_in_at,
                'check_out' => $day?->check_out_at,
                'status' => $display,
                'status_label' => $this->statusLabel($display),
                'flags' => $flags,
                'note' => $day?->note,
                'attendance_day_id' => $day?->id,
            ];

            if ($summary !== null) {
                $this->countSummary($summary, $day, $display, $flags);
            }

            yield $row;
        }
    }

    protected function countSummary(array &$summary, ?AttendanceDay $day, string $display, array $flags): void
    {
        $summary['scheduled']++;

        if ($day?->status === AttendanceStatus::Terlambat) {
            $summary['late']++;
            $summary['present']++;
        } elseif ($day?->status === AttendanceStatus::Hadir) {
            $summary['present']++;
        } elseif (in_array($day?->status, [AttendanceStatus::Izin, AttendanceStatus::Sakit, AttendanceStatus::Cuti], true)) {
            $summary['leave']++;
        } elseif ($display === 'belum_absen') {
            $summary['not_finalized']++;
        } else {
            $summary['absent']++;
        }

        if (in_array(Flags::PERLU_DITINJAU, $flags, true)) {
            $summary['pending_review']++;
        }
    }
}
