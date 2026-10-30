<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => 'Kantor '.fake()->unique()->city(),
            'address' => fake()->streetAddress(),
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'radius_meters' => 50,
            'is_active' => true,
        ];
    }
}
