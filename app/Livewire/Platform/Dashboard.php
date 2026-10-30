<?php

namespace App\Livewire\Platform;

use App\Enums\TenantStatus;
use App\Models\Invitation;
use App\Models\Membership;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(): View
    {
        $counts = [
            'total' => Tenant::count(),
            'pending' => Tenant::where('status', TenantStatus::Pending)->count(),
            'active' => Tenant::where('status', TenantStatus::Active)->count(),
            'suspended' => Tenant::where('status', TenantStatus::Suspended)->count(),
        ];

        return view('livewire.platform.dashboard', [
            'counts' => $counts,
            'members' => Membership::withoutGlobalScopes()->where('is_active', true)->count(),
            'pendingInvitations' => Invitation::withoutGlobalScopes()
                ->whereNull('accepted_at')
                ->whereNull('revoked_at')
                ->where('expires_at', '>', now())
                ->count(),
            'recent' => Tenant::orderByDesc('created_at')->limit(6)->get(),
        ])->layout('components.layouts.platform')->title('Platform');
    }
}
