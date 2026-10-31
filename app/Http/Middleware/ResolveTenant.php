<?php

namespace App\Http\Middleware;

use App\Enums\MembershipRole;
use App\Enums\TenantStatus;
use App\Models\Membership;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(protected TenantContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->is_platform_admin) {
            abort(403);
        }

        // Hanya tenant yang dibutuhkan untuk mengisi konteks. Lokasi dan divisi
        // sengaja tidak dimuat di sini: komponen yang memakainya sudah memuat
        // relasi itu sendiri, sehingga memuatnya di sini berarti dua query
        // tambahan pada setiap permintaan.
        $memberships = $user->activeMemberships()
            ->with('tenant')
            ->get()
            ->filter(fn (Membership $membership) => $membership->tenant !== null);

        if ($memberships->isEmpty()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda belum terhubung ke organisasi mana pun.']);
        }

        $selectedId = (int) $request->session()->get('active_tenant_id');
        $membership = $memberships->firstWhere('tenant_id', $selectedId);

        if (! $membership) {
            $membership = $memberships->first();
        }

        $tenant = $membership->tenant;

        if ($tenant->status === TenantStatus::Suspended
            && $membership->role !== MembershipRole::Owner) {
            abort(403, 'Organisasi ini sedang ditangguhkan.');
        }

        $request->session()->put('active_tenant_id', $tenant->id);

        $this->context->set($tenant, $membership);

        view()->share('currentTenant', $tenant);
        view()->share('currentMembership', $membership);
        view()->share('availableMemberships', $memberships);

        return $next($request);
    }
}
