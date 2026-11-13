<?php

namespace App\Http\Controllers;

use App\Enums\TenantStatus;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function __construct(protected InvitationService $invitations) {}

    public function show(string $token): View
    {
        $invitation = $this->invitations->findByToken($token);

        if (! $invitation) {
            return view('invitations.invalid', ['reason' => 'Undangan tidak ditemukan.']);
        }

        if (! $invitation->isPending()) {
            $reason = $invitation->accepted_at
                ? 'Undangan ini sudah diterima.'
                : ($invitation->revoked_at ? 'Undangan ini sudah dicabut.' : 'Undangan ini sudah kedaluwarsa.');

            return view('invitations.invalid', ['reason' => $reason]);
        }

        // BR-63: platform admin tidak boleh memiliki keanggotaan organisasi.
        if (Auth::user()?->is_platform_admin) {
            return view('invitations.invalid', ['reason' => 'Akun admin platform tidak dapat menerima undangan organisasi.']);
        }

        $existingUser = User::where('email', $invitation->email)->exists();

        return view('invitations.show', [
            'token' => $token,
            'invitation' => $invitation,
            'existingUser' => $existingUser,
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse|View
    {
        $invitation = $this->invitations->findByToken($token);

        if (! $invitation || ! $invitation->isPending()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Undangan ini sudah tidak berlaku.']);
        }

        $user = Auth::user();

        if ($user?->is_platform_admin) {
            return view('invitations.invalid', ['reason' => 'Akun admin platform tidak dapat menerima undangan organisasi.']);
        }

        if ($user && mb_strtolower($user->email) !== $invitation->email) {
            return back()->withErrors(['email' => 'Undangan ini ditujukan untuk alamat email lain. Keluar lalu buka tautan ini lagi.']);
        }

        if ($user) {
            $this->invitations->accept($invitation, $user);
            $request->session()->put('active_tenant_id', $invitation->tenant_id);

            return redirect()->route('app.home')
                ->with('status', 'Anda bergabung dengan '.$invitation->tenant->name.'.');
        }

        if (User::where('email', $invitation->email)->exists()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Email ini sudah terdaftar. Masuk untuk menerima undangan.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = $this->invitations->accept($invitation, null, $data['name'], $data['password']);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('active_tenant_id', $invitation->tenant_id);

        if ($invitation->tenant->status !== TenantStatus::Active) {
            return redirect()->route('tenant.status');
        }

        return redirect()->route('app.home')
            ->with('status', 'Anda bergabung dengan '.$invitation->tenant->name.'.');
    }
}
