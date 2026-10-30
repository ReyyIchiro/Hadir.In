<?php

namespace App\Providers;

use App\Models\AttendanceDay;
use App\Models\LeaveRequest;
use App\Models\Membership;
use App\Models\User;
use App\Policies\AttendancePolicy;
use App\Policies\LeavePolicy;
use App\Support\TenantContext;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Policy names do not follow the model name for two aggregates, so they
     * have to be mapped by hand. Without this every attendance and leave
     * screen aborts with 403.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        AttendanceDay::class => AttendancePolicy::class,
        LeaveRequest::class => LeavePolicy::class,
    ];

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
