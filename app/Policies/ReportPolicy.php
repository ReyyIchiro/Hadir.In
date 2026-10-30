<?php

namespace App\Policies;

use App\Models\Membership;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->actor($user)?->role->canReview() ?? false;
    }

    public function export(User $user): bool
    {
        return $this->viewAny($user);
    }

    protected function actor(User $user): ?Membership
    {
        if ($user->is_platform_admin) {
            return null;
        }

        return app(\App\Support\TenantContext::class)->membership();
    }
}
