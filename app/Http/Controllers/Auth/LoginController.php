<?php

namespace App\Http\Controllers\Auth;

use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Email atau kata sandi tidak cocok.']);
        }

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
