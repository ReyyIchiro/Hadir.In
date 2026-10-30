<?php

namespace Database\Factories;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\TenantSetting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = 'Organisasi '.fake()->unique()->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'timezone' => 'Asia/Jakarta',
            'status' => TenantStatus::Active,
            'plan' => 'standard',
            'trial_ends_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => TenantStatus::Pending, 'trial_ends_at' => now()->addDays(14)]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => TenantStatus::Suspended]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Tenant $tenant) {
            if (! $tenant->settings()->exists()) {
                TenantSetting::create(['tenant_id' => $tenant->id]);
            }
        });
    }
}
