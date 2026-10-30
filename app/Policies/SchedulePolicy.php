<?php

namespace App\Policies;

use App\Models\Membership;
use App\Models\Schedule;
use App\Models\User;
use App\Support\TenantContext;

class SchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->actor($user) !== null;
    }

    public function view(User $user, Schedule $schedule): bool
    {
        $actor = $this->actor($user);

        if (! $actor) {
            return false;
        }

        return $schedule->user_id === $user->id || $actor->role->canManageSchedules() || $actor->role->canReview();
    }

    public function create(User $user): bool
    {
        return $this->actor($user)?->role->canManageSchedules() ?? false;
    }

    public function update(User $user, Schedule $schedule): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Schedule $schedule): bool
    {
        return $this->create($user);
    }

    protected function actor(User $user): ?Membership
    {
        if ($user->is_platform_admin) {
            return null;
        }

        return app(TenantContext::class)->membership();
    }
}
