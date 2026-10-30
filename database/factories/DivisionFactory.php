<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Division>
 */
class DivisionFactory extends Factory
{
    protected $model = Division::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => 'Divisi '.fake()->unique()->word(),
            'description' => null,
            'is_active' => true,
        ];
    }
}
