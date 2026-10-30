<?php

namespace App\Livewire\Platform\Tenants;

use App\Models\Invitation;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Show extends Component
{
    public Tenant $tenant;

    public function render(): View
    {
        $activeMembers = $this->tenant->memberships()
            ->where('is_active', true)
            ->count();

        $pendingInvitations = Invitation::withoutGlobalScopes()
            ->where('tenant_id', $this->tenant->id)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->count();

        return view('livewire.platform.tenants.show', [
            'activeMembers' => $activeMembers,
            'pendingInvitations' => $pendingInvitations,
        ])->layout('components.layouts.platform')->title($this->tenant->name);
    }
}
