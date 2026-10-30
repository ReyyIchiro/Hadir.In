<?php

namespace App\Http\Controllers\Auth;

use App\Enums\MembershipRole;
use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantSwitchController extends Controller
{
    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $membership = $request->user()->membershipFor($tenant);

        if (! $membership) {
            abort(403);
        }

        if ($tenant->status !== TenantStatus::Active
            && $membership->role !== MembershipRole::Owner) {
            abort(403);
        }

        $request->session()->put('active_tenant_id', $tenant->id);

        if ($tenant->status !== TenantStatus::Active) {
            return redirect()->route('tenant.status');
        }

        return redirect()->route('app.home');
    }
}
