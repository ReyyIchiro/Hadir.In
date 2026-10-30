<?php

namespace App\Policies;

use App\Enums\ReviewStatus;
use App\Models\AttendanceReview;
use App\Models\Membership;
use App\Models\User;
use App\Support\TenantContext;

class AttendanceReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->actor($user)?->role->canReview() ?? false;
    }

    public function view(User $user, AttendanceReview $review): bool
    {
        $actor = $this->actor($user);

        if (! $actor || ! $actor->role->canReview()) {
            return false;
        }

        if ($actor->role->canManageMembers()) {
            return true;
        }

        if (! $actor->primary_location_id) {
            return false;
        }

        return $review->event?->location_id === $actor->primary_location_id
            || $review->user?->membershipFor($review->tenant)?->primary_location_id === $actor->primary_location_id;
    }

    public function decide(User $user, AttendanceReview $review): bool
    {
        return $this->view($user, $review) && $review->status === ReviewStatus::Pending;
    }

    protected function actor(User $user): ?Membership
    {
        if ($user->is_platform_admin) {
            return null;
        }

        return app(TenantContext::class)->membership();
    }
}
