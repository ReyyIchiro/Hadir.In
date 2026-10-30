<?php

namespace App\Providers;

use App\Models\Membership;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $resolve = function (User $user, ?Membership $membership = null): ?Membership {
            $membership ??= app(TenantContext::class)->membership();

            if ($membership === null || $membership->user_id !== $user->id) {
                return null;
            }

            return $membership->is_active ? $membership : null;
        };

        Gate::define('manage-members', fn (User $user, ?Membership $membership = null) => (bool) $resolve($user, $membership)?->role->canManageMembers());

        Gate::define('manage-settings', fn (User $user, ?Membership $membership = null) => (bool) $resolve($user, $membership)?->role->canManageSettings());

        Gate::define('manage-schedules', fn (User $user, ?Membership $membership = null) => (bool) $resolve($user, $membership)?->role->canManageSchedules());

        Gate::define('review-attendance', fn (User $user, ?Membership $membership = null) => (bool) $resolve($user, $membership)?->role->canReview());

        Gate::define('view-reports', fn (User $user, ?Membership $membership = null) => (bool) $resolve($user, $membership)?->role->canReview());
    }
}
