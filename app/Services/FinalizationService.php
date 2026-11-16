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

        $counts['finalized'] = $this->finalizeOpenDays($tenant, $settings, $now);
        $counts['absent'] = $this->markAbsent($tenant, $settings, $now);

        return $counts;
    }

    /**
     * Hari terbuka diproses dalam chunk berkolom minimal, lalu ditulis massal:
     * satu UPDATE per kelompok untuk hari yang sudah pulang, dan per baris
     * hanya untuk hari yang perlu penggabungan tanda.
     */
    protected function finalizeOpenDays(Tenant $tenant, TenantSetting $settings, CarbonImmutable $now): int
    {
        $noCheckout = [];
        $withCheckout = [];

        AttendanceDay::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereNull('finalized_at')
            ->whereNotNull('check_in_at')
            ->where('work_date', '<=', $now->toDateString())
            ->with(['schedule:id,end_time'])
            ->select(['id', 'schedule_id', 'work_date', 'check_out_at', 'flags'])
            ->chunkById(500, function ($days) use ($tenant, $settings, $now, &$noCheckout, &$withCheckout) {
                foreach ($days as $day) {
                    $endTime = $day->schedule?->end_time ?: $settings->work_end;
                    $deadline = $this->clock->localAt($tenant, $day->work_date->toDateString(), $endTime)
                        ->addMinutes($settings->checkout_late_minutes);

                    if ($now->lt($deadline)) {
                        continue;
                    }

                    if ($day->check_out_at === null) {
                        $flags = $day->flags ?? [];
                        $flags[] = Flags::FINAL_OTOMATIS;
                        $flags[] = Flags::TANPA_PULANG;

                        $noCheckout[] = [
                            'id' => $day->id,
                            'flags' => array_values(array_unique($flags)),
                        ];

                        continue;
                    }

                    $withCheckout[] = $day->id;
                }
            });

        $finalized = count($noCheckout) + count($withCheckout);

        if ($finalized === 0) {
            return 0;
        }

        DB::transaction(function () use ($noCheckout, $withCheckout) {
            $timestamp = now();

            foreach ($noCheckout as $item) {
                // Tanpa scope tabel: id berasal dari query AttendanceDay yang
                // sudah ter-scope tenant; lihat AGENTS.md.
                DB::table('attendance_days')
                    ->where('id', $item['id'])
                    ->update([
                        'flags' => json_encode($item['flags']),
                        'finalized_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
            }

            foreach (array_chunk($withCheckout, 500) as $chunk) {
                // Tanpa scope tabel: id berasal dari query ter-scope di atas.
                DB::table('attendance_days')
                    ->whereIn('id', $chunk)
                    ->update(['finalized_at' => $timestamp, 'updated_at' => $timestamp]);
            }
        });

        return $finalized;
    }

    /**
     * Hari kerja terlewat dalam jendela tujuh hari. Semua bahan diambil dalam
     * empat query, lalu hari absen disisipkan massal; keunikan
     * (tenant, anggota, tanggal) tetap menjaga dari duplikasi.
     */
    protected function markAbsent(Tenant $tenant, TenantSetting $settings, CarbonImmutable $now): int
    {
        $lookback = $now->subDays(7)->toDateString();
        $yesterday = $now->subDay()->toDateString();

        $schedules = Schedule::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('work_date', [$lookback, $yesterday])
            ->where('is_day_off', false)
            ->where('end_time', '!=', null)
            ->get(['id', 'user_id', 'work_date', 'end_time']);

        if ($schedules->isEmpty()) {
            return 0;
        }

        $holidays = Holiday::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('date', [$lookback, $yesterday])
            ->pluck('date')
            ->map(fn ($date) => CarbonImmutable::parse($date)->toDateString())
            ->all();

        $existing = AttendanceDay::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('work_date', [$lookback, $yesterday])
            ->get(['user_id', 'work_date'])
            ->map(fn (AttendanceDay $day) => $day->user_id.'|'.$day->work_date->toDateString())
            ->all();

        $leaveDates = [];

        LeaveRequest::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('status', LeaveStatus::Approved)
            ->where('start_date', '<=', $yesterday)
            ->where('end_date', '>=', $lookback)
            ->get(['user_id', 'start_date', 'end_date'])
            ->each(function (LeaveRequest $leave) use (&$leaveDates) {
                $cursor = CarbonImmutable::parse($leave->start_date);

                while ($cursor->lte(CarbonImmutable::parse($leave->end_date))) {
                    $leaveDates[$leave->user_id.'|'.$cursor->toDateString()] = true;
                    $cursor = $cursor->addDay();
                }
            });

        $rows = [];

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

            $key = $schedule->user_id.'|'.$date;

            if (in_array($key, $existing, true) || isset($leaveDates[$key])) {
                continue;
            }

            $rows[] = [
                'tenant_id' => $tenant->id,
                'user_id' => $schedule->user_id,
                'work_date' => $date,
                'schedule_id' => $schedule->id,
                'status' => AttendanceStatus::Absen->value,
                'late_minutes' => 0,
                'flags' => json_encode([Flags::FINAL_OTOMATIS]),
                'finalized_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $created = 0;

        foreach (array_chunk($rows, 500) as $chunk) {
            // Tanpa scope tabel: setiap baris memuat tenant_id eksplisit dari
            // $tenant; lihat AGENTS.md. insertOrIgnore menahan tabrakan dengan
            // hari yang dibuat bersamaan pada SQLite maupun PostgreSQL.
            $created += DB::table('attendance_days')->insertOrIgnore($chunk);
        }

        return $created;
    }
}
