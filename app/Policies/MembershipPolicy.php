<?php

namespace App\Policies;

use App\Enums\MembershipRole;
use App\Models\Membership;
use App\Models\User;

class MembershipPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->actor($user)?->role->canManageMembers() ?? false;
    }

    public function view(User $user, Membership $membership): bool
    {
        $actor = $this->actor($user);

        if (! $actor || $actor->tenant_id !== $membership->tenant_id) {
            return false;
        }

        return $actor->user_id === $membership->user_id || $actor->role->canManageMembers();
    }

    public function create(User $user): bool
    {
        return $this->actor($user)?->role->canManageMembers() ?? false;
    }

    public function update(User $user, Membership $membership): bool
    {
        $actor = $this->actor($user);

        if (! $actor || $actor->tenant_id !== $membership->tenant_id) {
            return false;
        }

        if ($membership->role === MembershipRole::Owner) {
            return false;
        }

        if ($actor->role === MembershipRole::Admin) {
            return $membership->role !== MembershipRole::Admin && $membership->role !== MembershipRole::Owner;
        }

        return $actor->role === MembershipRole::Owner;
    }

    public function delete(User $user, Membership $membership): bool
    {
        return $this->update($user, $membership);
    }

    protected function actor(User $user): ?Membership
    {
        if ($user->is_platform_admin) {
            return null;
        }

        return app(\App\Support\TenantContext::class)->membership();
    }
}
