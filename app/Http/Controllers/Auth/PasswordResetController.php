<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function request(): View
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink(
            ['email' => mb_strtolower(trim($request->input('email')))]
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Tautan atur ulang sudah dikirim bila email terdaftar.');
        }

        return back()->withErrors(['email' => 'Tautan tidak dapat dikirim. Periksa alamat email Anda.']);
    }

    public function edit(string $token): View
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->password = $password;
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Kata sandi sudah diperbarui. Masuk dengan kata sandi baru.');
        }

        return back()->withErrors(['email' => 'Tautan atur ulang tidak valid atau sudah kedaluwarsa.']);
    }
}
