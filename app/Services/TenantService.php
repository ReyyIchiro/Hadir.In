<?php

namespace App\Services;

use App\Enums\MembershipRole;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantService
{
    public function register(string $name, string $email, string $password, string $organization, string $timezone = 'Asia/Jakarta'): Tenant
    {
        return DB::transaction(function () use ($name, $email, $password, $organization, $timezone) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);

            $tenant = Tenant::create([
                'name' => $organization,
                'slug' => $this->uniqueSlug($organization),
                'timezone' => $timezone,
                'status' => \App\Enums\TenantStatus::Pending,
            ]);

            TenantSetting::create([
                'tenant_id' => $tenant->id,
            ]);

            $tenant->memberships()->create([
                'user_id' => $user->id,
                'role' => MembershipRole::Owner,
                'is_active' => true,
                'joined_at' => now(),
            ]);

            return $tenant;
        });
    }

    public function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'organisasi';
        $slug = $base;
        $counter = 2;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
