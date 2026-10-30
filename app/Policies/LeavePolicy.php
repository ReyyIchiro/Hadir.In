<?php

namespace App\Policies;

use App\Enums\MembershipRole;
use App\Models\LeaveRequest;
use App\Models\Membership;
use App\Models\User;

class LeavePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->actor($user) !== null;
    }

    public function view(User $user, LeaveRequest $leave): bool
    {
        $actor = $this->actor($user);

        if (! $actor || $leave->tenant_id !== $actor->tenant_id) {
            return false;
        }

        if ($leave->user_id === $user->id) {
            return true;
        }

        if ($actor->role->canManageMembers()) {
            return true;
        }

        return $actor->role === MembershipRole::Reviewer
            && $actor->primary_location_id
            && $leave->user?->membershipFor($leave->tenant)?->primary_location_id === $actor->primary_location_id;
    }

    public function create(User $user): bool
    {
        return $this->actor($user) !== null;
    }

    public function review(User $user, LeaveRequest $leave): bool
    {
        $actor = $this->actor($user);

        if (! $actor || ! $actor->role->canReview()) {
            return false;
        }

        if ($actor->role->canManageMembers()) {
            return true;
        }

        return $actor->primary_location_id
            && $leave->user?->membershipFor($leave->tenant)?->primary_location_id === $actor->primary_location_id;
    }

    public function cancel(User $user, LeaveRequest $leave): bool
    {
        if ($leave->user_id === $user->id) {
            return $leave->status === \App\Enums\LeaveStatus::Pending;
        }

        return $this->review($user, $leave) && $leave->status === \App\Enums\LeaveStatus::Approved;
    }

    protected function actor(User $user): ?Membership
    {
        if ($user->is_platform_admin) {
            return null;
        }

        return app(\App\Support\TenantContext::class)->membership();
    }
}
