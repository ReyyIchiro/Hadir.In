<?php

namespace App\Jobs;

use App\Models\AttendanceDay;
use App\Services\AttendanceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FinalizeAttendanceDay implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $attendanceDayId) {}

    public function handle(AttendanceService $attendance): void
    {
        $day = AttendanceDay::withoutGlobalScopes()->find($this->attendanceDayId);

        if ($day) {
            $attendance->finalizeDay($day);
        }
    }
}
