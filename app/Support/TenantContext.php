<?php

namespace App\Support;

use App\Models\Membership;
use App\Models\Tenant;

class TenantContext
{
    protected ?Tenant $tenant = null;

    protected ?Membership $membership = null;

    public function set(Tenant $tenant, Membership $membership): void
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
        return $this->tenant !== null && $this->membership !== null;
    }
}
