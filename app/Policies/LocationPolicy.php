<?php

namespace App\Policies;

use App\Models\Location;
use App\Models\Membership;
use App\Models\User;
use App\Support\TenantContext;

class LocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->actor($user)?->role->canManageSettings() ?? false;
    }

    public function view(User $user, Location $location): bool
    {
        return $this->owns($user, $location) && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Location $location): bool
    {
        return $this->owns($user, $location) && $this->viewAny($user);
    }

    public function delete(User $user, Location $location): bool
    {
        return $this->owns($user, $location) && $this->viewAny($user);
    }

    protected function owns(User $user, Location $location): bool
    {
        $actor = $this->actor($user);

        // Model binding sudah menyaring tenant, tapi policy tidak mengandalkan
        // binding saja: lokasi milik organisasi lain selalu ditolak.
        return $actor !== null && $actor->tenant_id === $location->tenant_id;
    }

    protected function actor(User $user): ?Membership
    {
        if ($user->is_platform_admin) {
            return null;
        }

        return app(TenantContext::class)->membership();
    }
}
