<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantStatusController extends Controller
{
    public function show(Request $request, TenantContext $context): View|RedirectResponse
    {
        $memberships = $request->user()->activeMemberships()->with('tenant')->get();

        if ($memberships->isEmpty()) {
            abort(403);
        }

        $tenantId = (int) $request->session()->get('active_tenant_id');
        $membership = $memberships->firstWhere('tenant_id', $tenantId) ?? $memberships->first();

        $context->set($membership->tenant, $membership);

        if ($membership->tenant->status === \App\Enums\TenantStatus::Active) {
            return redirect()->route('app.home');
        }

        return view('auth.tenant-status', [
            'tenant' => $membership->tenant,
            'membership' => $membership,
        ]);
    }
}
