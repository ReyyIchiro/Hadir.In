<?php

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;

it('membuat organisasi pending saat pendaftaran berhasil', function () {
    $response = $this->post('/daftar', [
        'name' => 'Rani Prameswari',
        'email' => 'rani@hadir.test',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'organization' => 'Toko Rani',
        'timezone' => 'Asia/Jakarta',
    ]);

    $response->assertRedirect('/status');

    $user = User::where('email', 'rani@hadir.test')->firstOrFail();
    $tenant = Tenant::where('name', 'Toko Rani')->firstOrFail();

    expect($tenant->status)->toBe(TenantStatus::Pending)
        ->and($tenant->slug)->toBe('toko-rani')
        ->and($tenant->settings)->not->toBeNull()
        ->and($tenant->memberships()->where('user_id', $user->id)->value('role')->value)->toBe('owner')
        ->and(auth()->id())->toBe($user->id);
});

it('menolak pendaftaran dengan email duplikat', function () {
    User::factory()->create(['email' => 'rani@hadir.test']);

    $response = $this->from('/daftar')->post('/daftar', [
        'name' => 'Rani Prameswari',
        'email' => 'rani@hadir.test',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'organization' => 'Toko Rani',
        'timezone' => 'Asia/Jakarta',
    ]);

    $response->assertRedirect('/daftar')->assertSessionHasErrors('email');
    expect(Tenant::count())->toBe(0);
});

it('menolak pendaftaran tanpa nama organisasi', function () {
    $response = $this->from('/daftar')->post('/daftar', [
        'name' => 'Rani Prameswari',
        'email' => 'rani@hadir.test',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'organization' => '',
        'timezone' => 'Asia/Jakarta',
    ]);

    $response->assertSessionHasErrors('organization');
    expect(Tenant::count())->toBe(0);
});

it('menolak zona waktu di luar daftar yang didukung', function () {
    $response = $this->from('/daftar')->post('/daftar', [
        'name' => 'Rani Prameswari',
        'email' => 'rani@hadir.test',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'organization' => 'Toko Rani',
        'timezone' => 'Europe/London',
    ]);

    $response->assertSessionHasErrors('timezone');
});

it('menampilkan halaman pendaftaran', function () {
    $this->get('/daftar')->assertOk()->assertSee('Daftar', false);
});
