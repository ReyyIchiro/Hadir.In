<?php

namespace App\Http\Controllers\Auth;

use App\Enums\MembershipRole;
use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['email'] = mb_strtolower(trim($credentials['email']));

        // Batas kedua per alamat IP: satu kantor di belakang satu IP publik
        // berbagi kuota, jadi hanya percobaan gagal yang dihitung.
        $ipKey = 'login-ip:'.$request->ip();

        if (RateLimiter::tooManyAttempts($ipKey, 20)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Terlalu banyak percobaan gagal dari jaringan Anda. Coba lagi dalam '
                        .RateLimiter::availableIn($ipKey).' detik.',
                ])
                ->setStatusCode(429);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($ipKey, 60);

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Email atau kata sandi tidak cocok.']);
        }

        RateLimiter::clear($ipKey);
        $request->session()->regenerate();
        $user = $request->user();

        if ($user->is_platform_admin) {
            return redirect()->intended(route('platform.dashboard'));
        }

        $memberships = $user->activeMemberships()->with('tenant')->get();

        if ($memberships->isEmpty()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'Akun Anda belum terhubung ke organisasi mana pun.']);
        }

        $tenant = $memberships->first()->tenant;
        // Hanya pemilik yang boleh masuk saat organisasi belum aktif. Anggota
        // dan peninjau baru bisa masuk setelah organisasi diaktifkan platform.
        if ($tenant->status !== TenantStatus::Active) {
            $membership = $memberships->firstWhere('tenant_id', $tenant->id);

            if ($membership?->role !== MembershipRole::Owner) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()
                    ->withInput($request->only('email'))
                    ->withErrors([
                        'email' => $tenant->status === TenantStatus::Suspended
                            ? 'Organisasi Anda sedang ditangguhkan. Hubungi pemilik organisasi.'
                            : 'Organisasi Anda belum aktif. Anda dapat masuk setelah pemilik mengaktifkannya.',
                    ]);
            }
        }

        $request->session()->put('active_tenant_id', $tenant->id);

        if ($tenant->status !== TenantStatus::Active) {
            return redirect()->route('tenant.status');
        }

        return redirect()->intended(route('app.home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
