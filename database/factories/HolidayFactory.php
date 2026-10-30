<?php

namespace Database\Factories;

use App\Models\Holiday;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Holiday>
 */
class HolidayFactory extends Factory
{
    protected $model = Holiday::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'date' => now()->addMonth()->toDateString(),
            'name' => 'Hari Libur '.fake()->word(),
        ];
    }
}
