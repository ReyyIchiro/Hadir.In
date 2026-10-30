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
use App\Support\Flags;
use App\Support\Waktu;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * @return array{rows: array<int, array<string, mixed>>, summary: array<string, int>, month: CarbonImmutable}
     */
    public function monthData(
        Tenant $tenant,
        string $month,
        ?int $divisionId = null,
        ?int $locationId = null,
        ?Membership $viewer = null,
    ): array {
        $start = CarbonImmutable::createFromFormat('Y-m', $month, $tenant->timezone)->startOfMonth();
        $end = $start->endOfMonth();
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();

        $memberships = $this->scopedMemberships($tenant, $divisionId, $locationId, $viewer);

        if ($memberships->isEmpty()) {
            return ['rows' => [], 'summary' => $this->emptySummary(), 'month' => $start];
        }

        $userIds = $memberships->pluck('user_id')->all();

        $schedules = Schedule::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereIn('user_id', $userIds)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->where('is_day_off', false)
            ->orderBy('work_date')
            ->get();

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
            ->get()
            ->keyBy(fn (AttendanceDay $day) => $day->user_id.'|'.$day->work_date->toDateString());

        $pendingReviews = AttendanceReview::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('status', ReviewStatus::Pending)
            ->whereIn('user_id', $userIds)
            ->whereBetween('created_at', [$start->setTimezone('UTC'), $end->endOfDay()->setTimezone('UTC')])
            ->get()
            ->groupBy('user_id');

        $rows = [];
        $summary = $this->emptySummary();

        foreach ($memberships as $membership) {
            $user = $membership->user;
            $primaryLocation = $membership->primaryLocation;

            $userSchedules = $schedules
                ->filter(fn (Schedule $schedule) => $schedule->user_id === $membership->user_id);

            foreach ($userSchedules as $schedule) {
                $date = $schedule->work_date->toDateString();

                if (in_array($date, $holidays, true)) {
                    continue;
                }

                $day = $days->get($membership->user_id.'|'.$date);
                $status = $day?->status;
                $display = $day?->displayStatus() ?? AttendanceStatus::Absen->value;

                $flags = $day?->flags ?? [];

                if ($day === null && $pendingReviews->has($membership->user_id)) {
                    $flags[] = Flags::PERLU_DITINJAU;
                }

                $rows[] = [
                    'user_id' => $membership->user_id,
                    'name' => $user?->name ?? '',
                    'email' => $user?->email ?? '',
                    'division' => $membership->division?->name ?? '',
                    'location' => $primaryLocation?->name ?? '',
                    'date' => $date,
                    'day_name' => Waktu::dayName($date),
                    'schedule' => substr((string) $schedule->start_time, 0, 5).'-'.substr((string) $schedule->end_time, 0, 5),
                    'check_in' => $day?->check_in_at,
                    'check_out' => $day?->check_out_at,
                    'status' => $display,
                    'status_label' => $this->statusLabel($display),
                    'flags' => array_values(array_unique($flags)),
                    'note' => $day?->note,
                    'attendance_day_id' => $day?->id,
                ];

                $summary['scheduled']++;

                if ($day?->status === AttendanceStatus::Terlambat) {
                    $summary['late']++;
                    $summary['present']++;
                } elseif ($day?->status === AttendanceStatus::Hadir) {
                    $summary['present']++;
                } elseif (in_array($day?->status, [AttendanceStatus::Izin, AttendanceStatus::Sakit, AttendanceStatus::Cuti], true)) {
                    $summary['leave']++;
                } else {
                    $summary['absent']++;
                }

                if (in_array(Flags::PERLU_DITINJAU, $flags, true)) {
                    $summary['pending_review']++;
                }
            }
        }

        usort($rows, fn ($a, $b) => [$a['date'], $a['name']] <=> [$b['date'], $b['name']]);

        return ['rows' => $rows, 'summary' => $summary, 'month' => $start];
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
            'pending_review' => 0,
        ];
    }
}
