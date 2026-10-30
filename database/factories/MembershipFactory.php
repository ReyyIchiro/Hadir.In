<?php

namespace Database\Factories;

use App\Enums\MembershipRole;
use App\Models\Membership;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    protected $model = Membership::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'role' => MembershipRole::Member,
            'primary_location_id' => null,
            'division_id' => null,
            'is_active' => true,
            'joined_at' => now(),
        ];
    }

    public function owner(): static
    {
        return $this->state(fn () => ['role' => MembershipRole::Owner]);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => MembershipRole::Admin]);
    }

    public function reviewer(): static
    {
        return $this->state(fn () => ['role' => MembershipRole::Reviewer]);
    }
}
