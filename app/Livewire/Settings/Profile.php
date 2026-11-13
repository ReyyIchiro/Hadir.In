<?php

namespace App\Livewire\Settings;

use App\Livewire\Concerns\WithTenant;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Profile extends Component
{
    use WithTenant;

    public string $name = '';

    public string $email = '';

    public string $original_email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $flash = null;

    public ?string $passwordFlash = null;

    public function mount(): void
    {
        $user = $this->currentUser();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->original_email = $user->email;
    }

    public function save(): void
    {
        $user = $this->currentUser();

        // Perubahan email dianggap aksi sensitif: wajib konfirmasi kata sandi
        // saat ini supaya sesi yang dibajak tidak bisa mengalihkan akun.
        $emailChanged = mb_strtolower(trim($this->email)) !== mb_strtolower($this->original_email);

        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email,'.$user->id],
            'current_password' => [$emailChanged ? 'required' : 'nullable', 'string'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'current_password.required' => 'Kata sandi saat ini wajib diisi untuk mengubah email.',
        ]);

        if ($emailChanged && ! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Kata sandi saat ini tidak cocok.');

            return;
        }

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->original_email = $user->email;
        $this->reset('current_password');
        $this->flash = 'Profil diperbarui.';
    }

    public function updatePassword(): void
    {
        $user = $this->currentUser();

        $this->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Kata sandi saat ini tidak cocok.');

            return;
        }

        $user->update(['password' => $this->password]);

        // Sesi lain diputus supaya perangkat yang tidak dikenal tetap keluar.
        Auth::logoutOtherDevices($this->password);

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->passwordFlash = 'Kata sandi diperbarui.';
    }

    public function render(): View
    {
        return view('livewire.settings.profile', [
            'membership' => $this->membership(),
            'tenant' => $this->tenant(),
        ])->layoutData(['heading' => 'Profil'])->title('Profil');
    }
}
