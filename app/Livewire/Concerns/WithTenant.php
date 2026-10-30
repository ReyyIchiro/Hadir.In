<?php

namespace App\Livewire\Concerns;

use App\Models\Membership;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;

trait WithTenant
{
    protected function tenant(): Tenant
    {
        $tenant = app(TenantContext::class)->tenant();

        abort_if($tenant === null, 403);

        return $tenant;
    }

    protected function membership(): Membership
    {
        $membership = app(TenantContext::class)->membership();

        abort_if($membership === null, 403);

        return $membership;
    }

    protected function currentUser(): User
    {
        return auth()->user();
    }

    protected function ensureInTenant(?int $tenantId): void
    {
        abort_unless($tenantId === $this->tenant()->id, 404);
    }
}
