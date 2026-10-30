<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('membuat platform admin baru dengan kata sandi sementara', function () {
    $this->artisan('platform:promote', ['email' => 'baru@hadir.test'])
        ->expectsOutputToContain('Pengguna baru dibuat.')
        ->assertSuccessful();

    $user = User::where('email', 'baru@hadir.test')->first();

    expect($user)->not->toBeNull()
        ->and($user->is_platform_admin)->toBeTrue()
        ->and($user->memberships()->exists())->toBeFalse()
        ->and(Hash::needsRehash($user->password))->toBeFalse();
});

it('menolak mempromosikan pengguna yang punya keanggotaan', function () {
    $tenant = makeTenant();
    $user = makeUser(['email' => 'anggota@hadir.test']);
    makeMembership($tenant, $user, 'member');

    $this->artisan('platform:promote', ['email' => 'anggota@hadir.test'])
        ->expectsOutputToContain('tidak boleh memiliki keanggotaan')
        ->assertFailed();

    expect($user->refresh()->is_platform_admin)->toBeFalse();
});

it('menolak alamat email yang tidak valid', function () {
    $this->artisan('platform:promote', ['email' => 'bukan-email'])
        ->expectsOutputToContain('tidak valid')
        ->assertFailed();
});

it('tidak mengubah apa pun bila pengguna sudah platform admin', function () {
    $user = User::create([
        'name' => 'Admin Platform',
        'email' => 'sudah@hadir.test',
        'password' => Hash::make('rahasia123'),
        'is_platform_admin' => true,
    ]);

    $this->artisan('platform:promote', ['email' => 'sudah@hadir.test'])
        ->expectsOutputToContain('sudah menjadi platform admin')
        ->assertSuccessful();

    expect($user->refresh()->is_platform_admin)->toBeTrue();
});
