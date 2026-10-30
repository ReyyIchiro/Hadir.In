<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceDay;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceDay>
 */
class AttendanceDayFactory extends Factory
{
    protected $model = AttendanceDay::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'work_date' => now()->toDateString(),
            'status' => AttendanceStatus::Hadir,
            'check_in_at' => now()->setTime(0, 56),
            'check_out_at' => null,
            'late_minutes' => 0,
            'early_leave_minutes' => 0,
            'flags' => [],
            'is_corrected' => false,
        ];
    }
}
