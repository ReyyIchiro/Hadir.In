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

        // Konteks dibersihkan lebih dulu: singleton ini boleh terbawa dari
        // permintaan sebelumnya pada worker yang berumur panjang. Scope tenant
        // tidak boleh mewarisi sisa permintaan lama.
        $this->context->clear();

        // Query keanggotaan harus lintas organisasi: middleware inilah yang
        // memilih organisasi aktif, jadi scope tenant belum boleh aktif di
        // sini. Tanpa tanpaGlobalScopes, keanggotaan tenant lain tidak terlihat
        // dan perpindahan organisasi diam-diam gagal.
        $memberships = Membership::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('is_active', true)
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

        // Anggota organisasi yang ditangguhkan diarahkan ke halaman status,
        // bukan ditolak keras: pengguna dengan beberapa keanggotaan tetap bisa
        // berpindah ke organisasi aktif dari halaman itu (BR-64).
        if ($tenant->status === TenantStatus::Suspended
            && $membership->role !== MembershipRole::Owner) {
            return redirect()->route('tenant.status')
                ->withErrors(['status' => 'Organisasi ini sedang ditangguhkan.']);
        }

        $request->session()->put('active_tenant_id', $tenant->id);

        $this->context->set($tenant, $membership);

        view()->share('currentTenant', $tenant);
        view()->share('currentMembership', $membership);
        view()->share('availableMemberships', $memberships);

        return $next($request);
    }
}
