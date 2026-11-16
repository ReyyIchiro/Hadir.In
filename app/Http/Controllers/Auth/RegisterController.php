<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(protected TenantService $tenants) {}

    public function show(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'organization' => ['required', 'string', 'max:120'],
            'timezone' => ['required', 'in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura'],
            // Honeypot: kolom tersembunyi untuk manusia. Bot yang mengisinya
            // diblokir tanpa memberi tahu bahwa ini penangkal bot.
            'website' => ['prohibited'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar. Masuk dengan akun tersebut.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'website.prohibited' => 'Pendaftaran tidak dapat diproses.',
        ]);

        $tenant = $this->tenants->register(
            $data['name'],
            $data['email'],
            $data['password'],
            $data['organization'],
            $data['timezone'],
        );

        Auth::login($tenant->memberships()->first()->user);
        $request->session()->regenerate();
        $request->session()->put('active_tenant_id', $tenant->id);

        return redirect()->route('tenant.status');
    }
}
