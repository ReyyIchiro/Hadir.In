<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleService
{
    /**
     * @param  array{start_time?: ?string, end_time?: ?string, break_start?: ?string, break_end?: ?string, is_day_off?: bool, note?: ?string}  $data
     */
    public function save(Schedule $schedule, array $data): Schedule
    {
        $data = $this->normalize($data);

        $schedule->update($data);

        return $schedule;
    }

    /**
     * @param  array{user_ids: array<int, int>, start_date: string, end_date: string, weekdays: array<int, int>, mode: string}  $data
     * @return array{created: int, updated: int, skipped: int}
     */
    public function bulkApply(Tenant $tenant, array $data): array
    {
        $start = CarbonImmutable::parse($data['start_date']);
        $end = CarbonImmutable::parse($data['end_date']);

        if ($end->lt($start)) {
            throw ValidationException::withMessages(['end_date' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.']);
        }

        if ($start->diffInDays($end) + 1 > 92) {
            throw ValidationException::withMessages(['end_date' => 'Rentang penerapan maksimal 92 hari.']);
        }

        $weekdays = array_map('intval', $data['weekdays'] ?? []);

        if (empty($weekdays)) {
            throw ValidationException::withMessages(['weekdays' => 'Pilih minimal satu hari dalam minggu.']);
        }

        $userIds = $tenant->memberships()
            ->whereIn('user_id', $data['user_ids'] ?? [])
            ->where('is_active', true)
            ->pluck('user_id')
            ->all();

        if (empty($userIds)) {
            throw ValidationException::withMessages(['user_ids' => 'Pilih minimal satu anggota.']);
        }

        $payload = $this->normalize($data);
        unset($payload['mode'], $payload['user_ids'], $payload['start_date'], $payload['end_date'], $payload['weekdays']);

        $counts = ['created' => 0, 'updated' => 0, 'skipped' => 0];
        $mode = ($data['mode'] ?? 'skip') === 'overwrite' ? 'overwrite' : 'skip';

        DB::transaction(function () use ($tenant, $userIds, $start, $end, $weekdays, $payload, $mode, &$counts) {
            $cursor = $start->copy();

            while ($cursor->lte($end)) {
                if (in_array($cursor->dayOfWeekIso, $weekdays, true)) {
                    foreach ($userIds as $userId) {
                        $existing = Schedule::withoutGlobalScopes()
                            ->where('tenant_id', $tenant->id)
                            ->where('user_id', $userId)
                            ->where('work_date', $cursor->toDateString())
                            ->first();

                        if ($existing) {
                            if ($mode === 'skip') {
                                $counts['skipped']++;

                                continue;
                            }

                            $existing->update($payload);
                            $counts['updated']++;

                            continue;
                        }

                        Schedule::create(array_merge($payload, [
                            'tenant_id' => $tenant->id,
                            'user_id' => $userId,
                            'work_date' => $cursor->toDateString(),
                        ]));
                        $counts['created']++;
                    }
                }

                $cursor = $cursor->addDay();
            }
        });

        return $counts;
    }

    /**
     * @return array{created: int, updated: int, skipped: int}
     */
    public function preview(Tenant $tenant, array $data): array
    {
        $start = CarbonImmutable::parse($data['start_date'] ?? 'today');
        $end = CarbonImmutable::parse($data['end_date'] ?? 'today');

        if ($end->lt($start)) {
            return ['created' => 0, 'updated' => 0, 'skipped' => 0];
        }

        $weekdays = array_map('intval', $data['weekdays'] ?? []);
        $userIds = $tenant->memberships()
            ->whereIn('user_id', $data['user_ids'] ?? [])
            ->where('is_active', true)
            ->pluck('user_id')
            ->all();

        $dates = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if (in_array($cursor->dayOfWeekIso, $weekdays, true)) {
                $dates[] = $cursor->toDateString();
            }

            $cursor = $cursor->addDay();
        }

        if (empty($dates) || empty($userIds)) {
            return ['created' => 0, 'updated' => 0, 'skipped' => 0];
        }

        $existing = Schedule::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereIn('user_id', $userIds)
            ->whereIn('work_date', $dates)
            ->count();

        $total = count($dates) * count($userIds);

        return [
            'created' => $total - $existing,
            'updated' => ($data['mode'] ?? 'skip') === 'overwrite' ? $existing : 0,
            'skipped' => ($data['mode'] ?? 'skip') === 'overwrite' ? 0 : $existing,
        ];
    }

    public function deleteForDate(Tenant $tenant, User $user, string $date): void
    {
        Schedule::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->where('work_date', $date)
            ->delete();
    }

    public function holidayExists(string $date): bool
    {
        return Holiday::where('date', $date)->exists();
    }

    protected function normalize(array $data): array
    {
        $isDayOff = (bool) ($data['is_day_off'] ?? false);

        $start = $data['start_time'] ?? null;
        $end = $data['end_time'] ?? null;
        $breakStart = $data['break_start'] ?? null;
        $breakEnd = $data['break_end'] ?? null;

        if ($isDayOff) {
            return [
                'is_day_off' => true,
                'start_time' => null,
                'end_time' => null,
                'break_start' => null,
                'break_end' => null,
                'note' => $data['note'] ?? null,
            ];
        }

        if (! $start || ! $end) {
            throw ValidationException::withMessages(['start_time' => 'Jam mulai dan jam selesai wajib diisi.']);
        }

        if (strtotime($end) <= strtotime($start)) {
            throw ValidationException::withMessages(['end_time' => 'Jam selesai harus setelah jam mulai.']);
        }

        if (($breakStart && ! $breakEnd) || (! $breakStart && $breakEnd)) {
            throw ValidationException::withMessages(['break_start' => 'Jam istirahat harus diisi keduanya atau dikosongkan.']);
        }

        if ($breakStart && $breakEnd) {
            if (strtotime($breakEnd) <= strtotime($breakStart)) {
                throw ValidationException::withMessages(['break_end' => 'Jam istirahat selesai harus setelah mulai.']);
            }

            if (strtotime($breakStart) < strtotime($start) || strtotime($breakEnd) > strtotime($end)) {
                throw ValidationException::withMessages(['break_start' => 'Jam istirahat harus berada di dalam jam kerja.']);
            }
        }

        return [
            'is_day_off' => false,
            'start_time' => $start,
            'end_time' => $end,
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
            'note' => $data['note'] ?? null,
        ];
    }
}
