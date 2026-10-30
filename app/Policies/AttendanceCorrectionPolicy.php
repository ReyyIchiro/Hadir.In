<?php

namespace App\Policies;

use App\Enums\CorrectionStatus;
use App\Models\AttendanceCorrection;
use App\Models\Membership;
use App\Models\User;
use App\Support\TenantContext;

class AttendanceCorrectionPolicy
{
    public function view(User $user, AttendanceCorrection $correction): bool
    {
        $actor = $this->actor($user);

        if (! $actor || ! $actor->role->canReview()) {
            return $correction->user_id === $user->id;
        }

        if ($actor->role->canManageMembers()) {
            return true;
        }

        if (! $actor->primary_location_id) {
            return false;
        }

        return $correction->user?->membershipFor($correction->tenant)?->primary_location_id === $actor->primary_location_id;
    }

    public function decide(User $user, AttendanceCorrection $correction): bool
    {
        return $this->view($user, $correction) && $correction->status === CorrectionStatus::Pending;
    }

    protected function actor(User $user): ?Membership
    {
        if ($user->is_platform_admin) {
            return null;
        }

        return app(TenantContext::class)->membership();
    }
}
