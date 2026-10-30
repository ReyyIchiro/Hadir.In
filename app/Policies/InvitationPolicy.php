<?php

namespace App\Policies;

use App\Models\Invitation;
use App\Models\Membership;
use App\Models\User;
use App\Support\TenantContext;

class InvitationPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->actor($user)?->role->canManageMembers() ?? false;
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Invitation $invitation): bool
    {
        $actor = $this->actor($user);

        return $actor !== null
            && $actor->tenant_id === $invitation->tenant_id
            && $actor->role->canManageMembers();
    }

    protected function actor(User $user): ?Membership
    {
        if ($user->is_platform_admin) {
            return null;
        }

        return app(TenantContext::class)->membership();
    }
}
