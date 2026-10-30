<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Tenant;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class TenantClock
{
    public function now(Tenant $tenant): CarbonImmutable
    {
        return CarbonImmutable::now($tenant->timezone);
    }

    public function today(Tenant $tenant): CarbonImmutable
    {
        return $this->now($tenant)->startOfDay();
    }

    public function local(CarbonInterface $value, Tenant $tenant): CarbonImmutable
    {
        return CarbonImmutable::instance($value)->setTimezone($tenant->timezone);
    }

    public function utc(CarbonInterface $value, Tenant $tenant): CarbonImmutable
    {
        return CarbonImmutable::instance($value)->setTimezone('UTC');
    }

    /**
     * Gabungkan tanggal kerja lokal dengan jam lokal menjadi waktu UTC.
     */
    public function atLocal(Tenant $tenant, string $workDate, string $time): CarbonImmutable
    {
        return $this->localAt($tenant, $workDate, $time)->setTimezone('UTC');
    }

    /**
     * Jam lokal pada tanggal kerja tertentu.
     */
    public function localAt(Tenant $tenant, string $workDate, string $time): CarbonImmutable
    {
        $time = strlen($time) === 5 ? $time.':00' : $time;

        return CarbonImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $workDate.' '.$time,
            $tenant->timezone,
        );
    }

    public function lateMinutes(CarbonInterface $checkInAt, CarbonImmutable $scheduledStart, int $graceMinutes): int
    {
        $localCheckIn = CarbonImmutable::instance($checkInAt)->setTimezone($scheduledStart->getTimezone());
        $diff = (int) floor($scheduledStart->diffInMinutes($localCheckIn, false));

        if ($diff <= $graceMinutes) {
            return 0;
        }

        return max(0, $diff);
    }

    public function earlyLeaveMinutes(CarbonInterface $checkOutAt, CarbonImmutable $scheduledEnd): int
    {
        $localCheckOut = CarbonImmutable::instance($checkOutAt)->setTimezone($scheduledEnd->getTimezone());
        $diff = (int) floor($scheduledEnd->diffInMinutes($localCheckOut, false));

        return $diff > 0 ? $diff : 0;
    }

    public function statusFor(int $lateMinutes): AttendanceStatus
    {
        return $lateMinutes > 0 ? AttendanceStatus::Terlambat : AttendanceStatus::Hadir;
    }
}
