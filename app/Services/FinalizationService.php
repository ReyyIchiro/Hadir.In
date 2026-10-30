<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\LeaveStatus;
use App\Models\AttendanceDay;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Support\Flags;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class FinalizationService
{
    public function __construct(protected TenantClock $clock) {}

    /**
     * @return array{finalized: int, absent: int}
     */
    public function runForTenant(Tenant $tenant): array
    {
        $settings = $tenant->settings ?? new TenantSetting(['tenant_id' => $tenant->id]);
        $now = $this->clock->now($tenant);
        $counts = ['finalized' => 0, 'absent' => 0];

        $openDays = AttendanceDay::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereNull('finalized_at')
            ->whereNotNull('check_in_at')
            ->get();

        foreach ($openDays as $day) {
            $endTime = $day->schedule?->end_time ?: $settings->work_end;
            $scheduledEnd = $this->clock->localAt($tenant, $day->work_date->toDateString(), $endTime);
            $deadline = $scheduledEnd->addMinutes($settings->checkout_late_minutes);

            if ($now->lt($deadline)) {
                continue;
            }

            DB::transaction(function () use ($day) {
                $flags = $day->flags ?? [];
                $flags[] = Flags::FINAL_OTOMATIS;

                if ($day->check_out_at === null) {
                    $flags[] = Flags::TANPA_PULANG;
                }

                $day->update([
                    'flags' => array_values(array_unique($flags)),
                    'finalized_at' => now(),
                ]);
            });

            $counts['finalized']++;
        }

        $counts['absent'] = $this->markAbsent($tenant, $settings, $now);

        return $counts;
    }

    protected function markAbsent(Tenant $tenant, TenantSetting $settings, CarbonImmutable $now): int
    {
        $lookback = $now->subDays(7)->toDateString();
        $yesterday = $now->subDay()->toDateString();

        $schedules = Schedule::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('work_date', [$lookback, $yesterday])
            ->where('is_day_off', false)
            ->where('end_time', '!=', null)
            ->get();

        $holidays = Holiday::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('date', [$lookback, $yesterday])
            ->pluck('date')
            ->map(fn ($date) => CarbonImmutable::parse($date)->toDateString())
            ->all();

        $created = 0;

        foreach ($schedules as $schedule) {
            $date = $schedule->work_date->toDateString();

            if (in_array($date, $holidays, true)) {
                continue;
            }

            $deadline = $this->clock->localAt($tenant, $date, $schedule->end_time)
                ->addMinutes($settings->checkout_late_minutes);

            if ($now->lt($deadline)) {
                continue;
            }

            $exists = AttendanceDay::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('user_id', $schedule->user_id)
                ->where('work_date', $date)
                ->exists();

            if ($exists) {
                continue;
            }

            $leaveExists = LeaveRequest::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('user_id', $schedule->user_id)
                ->where('status', LeaveStatus::Approved)
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->exists();

            if ($leaveExists) {
                continue;
            }

            AttendanceDay::create([
                'tenant_id' => $tenant->id,
                'user_id' => $schedule->user_id,
                'work_date' => $date,
                'schedule_id' => $schedule->id,
                'status' => AttendanceStatus::Absen,
                'late_minutes' => 0,
                'flags' => [Flags::FINAL_OTOMATIS],
                'finalized_at' => now(),
            ]);

            $created++;
        }

        return $created;
    }
}
