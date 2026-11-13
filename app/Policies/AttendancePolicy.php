<?php

namespace App\Policies;

use App\Enums\MembershipRole;
use App\Models\AttendanceDay;
use App\Models\Membership;
use App\Models\User;
use App\Support\TenantContext;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->actor($user) !== null;
    }

    public function view(User $user, AttendanceDay $day): bool
    {
        $actor = $this->actor($user);

        if (! $actor || $day->tenant_id !== $actor->tenant_id) {
            return false;
        }

        if ($day->user_id === $user->id) {
            return true;
        }

        if ($actor->role->canManageMembers()) {
            return true;
        }

        return $actor->role === MembershipRole::Reviewer && $this->inReviewScope($actor, $day);
    }

    public function create(User $user): bool
    {
        return $this->actor($user) !== null;
    }

    public function checkOut(User $user): bool
    {
        return $this->actor($user) !== null;
    }

    public function review(User $user, AttendanceDay $day): bool
    {
        $actor = $this->actor($user);

        if (! $actor || ! $actor->role->canReview()) {
            return false;
        }

        // Pemilik hari presensi tidak meninjau kejadian miliknya sendiri.
        if ($day->user_id === $user->id) {
            return false;
        }

        if ($actor->role->canManageMembers()) {
            return true;
        }

        return $this->inReviewScope($actor, $day);
    }

    public function requestCorrection(User $user, AttendanceDay $day): bool
    {
        if ($day->user_id !== $user->id) {
            return false;
        }

        return $day->work_date->diffInDays(now(), true) <= 7;
    }

    public function viewSelfie(User $user, AttendanceDay $day): bool
    {
        return $this->view($user, $day);
    }

    public function inReviewScope(Membership $actor, AttendanceDay $day): bool
    {
        if (! $actor->primary_location_id) {
            return false;
        }

        if ($day->location_id === $actor->primary_location_id) {
            return true;
        }

        $memberLocation = $day->user?->membershipFor($day->tenant)?->primary_location_id;

        return $memberLocation === $actor->primary_location_id;
    }

    protected function actor(User $user): ?Membership
    {
        if ($user->is_platform_admin) {
            return null;
        }

        return app(TenantContext::class)->membership();
    }
}
