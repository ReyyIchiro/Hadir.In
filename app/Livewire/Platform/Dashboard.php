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
        // Satu query agregat menggantikan lima query hitung terpisah.
        $byStatus = Tenant::toBase()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $counts = [
            'total' => (int) $byStatus->sum(),
            'pending' => (int) $byStatus->get(TenantStatus::Pending->value, 0),
            'active' => (int) $byStatus->get(TenantStatus::Active->value, 0),
            'suspended' => (int) $byStatus->get(TenantStatus::Suspended->value, 0),
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
