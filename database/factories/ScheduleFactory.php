<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'work_date' => now()->toDateString(),
            'is_day_off' => false,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'break_start' => null,
            'break_end' => null,
            'note' => null,
        ];
    }

    public function dayOff(): static
    {
        return $this->state(fn () => ['is_day_off' => true, 'start_time' => null, 'end_time' => null]);
    }
}
