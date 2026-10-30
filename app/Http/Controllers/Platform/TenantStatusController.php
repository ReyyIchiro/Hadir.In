<?php

namespace App\Http\Controllers\Platform;

use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantStatusController extends Controller
{
    public function activate(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($tenant->status === TenantStatus::Pending, 422, 'Hanya organisasi yang menunggu aktivasi dapat diaktifkan.');

        $tenant->update([
            'status' => TenantStatus::Active,
            'trial_ends_at' => $tenant->trial_ends_at ?? now()->addDays(14),
        ]);

        return back()->with('status', 'Organisasi '.$tenant->name.' diaktifkan.');
    }

    public function suspend(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_if($tenant->status === TenantStatus::Suspended, 422, 'Organisasi sudah ditangguhkan.');

        $tenant->update(['status' => TenantStatus::Suspended]);

        return back()->with('status', 'Organisasi '.$tenant->name.' ditangguhkan.');
    }

    public function restore(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($tenant->status === TenantStatus::Suspended, 422, 'Organisasi tidak sedang ditangguhkan.');

        $tenant->update(['status' => TenantStatus::Active]);

        return back()->with('status', 'Organisasi '.$tenant->name.' aktif kembali.');
    }
}
