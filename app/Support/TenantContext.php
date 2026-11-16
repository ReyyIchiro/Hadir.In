<?php

namespace App\Support;

use App\Models\Membership;
use App\Models\Tenant;

class TenantContext
{
    protected ?Tenant $tenant = null;

    protected ?Membership $membership = null;

    public function set(Tenant $tenant, ?Membership $membership = null): void
    {
        $this->tenant = $tenant;
        $this->membership = $membership;
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function membership(): ?Membership
    {
        return $this->membership;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function clear(): void
    {
        $this->tenant = null;
        $this->membership = null;
    }

    public function hasTenant(): bool
    {
        // Membership boleh null pada job konsol: scope tetap aktif asalkan
        // tenant diset, jadi query job tidak pernah kelepasan filter tenant.
        return $this->tenant !== null;
    }
}
