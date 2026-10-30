<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PromotePlatformAdmin extends Command
{
    protected $signature = 'platform:promote
        {email : Alamat email pengguna}
        {--name= : Nama bila pengguna baru dibuat}';

    protected $description = 'Menjadikan pengguna sebagai platform admin.';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Alamat email tidak valid.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user === null) {
            $password = Str::password(16, symbols: false);

            $user = User::create([
                'name' => $this->option('name') ?: Str::headline(Str::before($email, '@')),
                'email' => $email,
                'password' => Hash::make($password),
                'is_platform_admin' => true,
            ]);

            $this->info('Pengguna baru dibuat.');
            $this->line('Email: '.$user->email);
            $this->line('Kata sandi sementara: '.$password);
            $this->warn('Simpan kata sandi ini sekarang. Kata sandi tidak ditampilkan lagi.');

            return self::SUCCESS;
        }

        if ($user->memberships()->exists()) {
            $this->error('Pengguna ini memiliki keanggotaan organisasi. Platform admin tidak boleh memiliki keanggotaan.');

            return self::FAILURE;
        }

        if ($user->is_platform_admin) {
            $this->info('Pengguna ini sudah menjadi platform admin.');

            return self::SUCCESS;
        }

        $user->update(['is_platform_admin' => true]);
        $this->info('Pengguna '.$user->email.' sekarang menjadi platform admin.');

        return self::SUCCESS;
    }
}
