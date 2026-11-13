<?php

namespace App\Http\Controllers\Auth;

use App\Enums\MembershipRole;
use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantStatusController extends Controller
{
    public function show(Request $request, TenantContext $context): View|RedirectResponse
    {
        // Halaman status justru menampilkan organisasi yang belum aktif, jadi
        // daftar keanggotaan diambil lintas tenant sebelum konteks diisi.
        $memberships = Membership::withoutGlobalScopes()
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->with('tenant')
            ->get()
            ->filter(fn (Membership $membership) => $membership->tenant !== null);

        if ($memberships->isEmpty()) {
            abort(403);
        }

        $tenantId = (int) $request->session()->get('active_tenant_id');
        $membership = $memberships->firstWhere('tenant_id', $tenantId) ?? $memberships->first();

        $context->set($membership->tenant, $membership);

        if ($membership->tenant->status === TenantStatus::Active) {
            return redirect()->route('app.home');
        }

        // Organisasi lain yang masih bisa dibuka: aktif untuk semua peran,
        // atau pending bila pengguna adalah pemiliknya (BR-64).
        $otherMemberships = $memberships
            ->reject(fn ($other) => $other->tenant_id === $membership->tenant_id)
            ->filter(function ($other) {
                if ($other->tenant === null) {
                    return false;
                }

                return $other->tenant->status === TenantStatus::Active
                    || ($other->tenant->status === TenantStatus::Pending
                        && $other->role === MembershipRole::Owner);
            })
            ->values();

        return view('auth.tenant-status', [
            'tenant' => $membership->tenant,
            'membership' => $membership,
            'otherMemberships' => $otherMemberships,
        ]);
    }
}
