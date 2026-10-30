<?php

namespace App\Providers;

use App\Enums\MembershipRole;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('manage-members', function (User $user, Membership $membership) {
            if ($membership->user_id !== $user->id) {
                return false;
            }

            return $membership->is_active && $membership->role->canManageMembers();
        });

        Gate::define('manage-settings', function (User $user, Membership $membership) {
            if ($membership->user_id !== $user->id) {
                return false;
            }

            return $membership->is_active && $membership->role->canManageSettings();
        });

        Gate::define('manage-schedules', function (User $user, Membership $membership) {
            if ($membership->user_id !== $user->id) {
                return false;
            }

            return $membership->is_active && $membership->role->canManageSchedules();
        });

        Gate::define('review-attendance', function (User $user, Membership $membership) {
            if ($membership->user_id !== $user->id) {
                return false;
            }

            return $membership->is_active && $membership->role->canReview();
        });

        Gate::define('view-reports', function (User $user, Membership $membership) {
            if ($membership->user_id !== $user->id) {
                return false;
            }

            return $membership->is_active && $membership->role->canReview();
        });
    }
}
