<?php

it('mengarahkan anggota tenant aktif ke aplikasi', function () {
    $tenant = makeTenant();
    $user = makeUser(['email' => 'anggota@hadir.test']);
    makeMembership($tenant, $user, 'member');

    $response = $this->post('/masuk', [
        'email' => 'anggota@hadir.test',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/app');
    expect(auth()->id())->toBe($user->id);
});

it('mengarahkan pemilik tenant pending ke halaman status', function () {
    $tenant = makeTenant(['status' => 'pending']);
    $user = makeUser(['email' => 'pemilik@hadir.test']);
    makeMembership($tenant, $user, 'owner');

    $response = $this->post('/masuk', [
        'email' => 'pemilik@hadir.test',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/status');
});

it('menolak kata sandi yang salah', function () {
    $user = makeUser(['email' => 'anggota@hadir.test']);

    $response = $this->from('/masuk')->post('/masuk', [
        'email' => 'anggota@hadir.test',
        'password' => 'salah-total',
    ]);

    $response->assertRedirect('/masuk')->assertSessionHasErrors('email');
    expect(auth()->check())->toBeFalse();
});

it('membatasi percobaan masuk menjadi lima per menit', function () {
    $user = makeUser(['email' => 'anggota@hadir.test']);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/masuk', ['email' => 'anggota@hadir.test', 'password' => 'salah']);
    }

    $response = $this->post('/masuk', ['email' => 'anggota@hadir.test', 'password' => 'salah']);

    $response->assertStatus(429);
});

it('membatasi percobaan gagal lintas email dari satu alamat IP', function () {
    // Dua puluh email berbeda dengan satu percobaan gagal masing-masing
    // lolos batas lima-per-email, lalu batas per IP mengunci yang berikut.
    for ($i = 0; $i < 20; $i++) {
        $this->post('/masuk', ['email' => 'penyerang-'.$i.'@hadir.test', 'password' => 'salah']);
    }

    $response = $this->post('/masuk', ['email' => 'korban@hadir.test', 'password' => 'salah']);

    $response->assertStatus(429);
});

it('mengeluarkan pengguna dan menghapus sesi', function () {
    $user = makeUser();

    $this->actingAs($user)->post('/keluar')->assertRedirect('/');
    expect(auth()->check())->toBeFalse();
});

it('menolak masuk untuk akun tanpa organisasi', function () {
    makeUser(['email' => 'tanpa@hadir.test']);

    $response = $this->from('/masuk')->post('/masuk', [
        'email' => 'tanpa@hadir.test',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    expect(auth()->check())->toBeFalse();
});

it('menolak anggota masuk saat organisasi belum aktif', function () {
    $tenant = makeTenant(['status' => 'pending']);
    $user = makeUser(['email' => 'anggota@hadir.test']);
    makeMembership($tenant, $user, 'member');

    $response = $this->from('/masuk')->post('/masuk', [
        'email' => 'anggota@hadir.test',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/masuk')->assertSessionHasErrors('email');
    expect(auth()->check())->toBeFalse();
});

it('menolak anggota masuk saat organisasi ditangguhkan', function () {
    $tenant = makeTenant(['status' => 'suspended']);
    $user = makeUser(['email' => 'anggota@hadir.test']);
    makeMembership($tenant, $user, 'member');

    $response = $this->from('/masuk')->post('/masuk', [
        'email' => 'anggota@hadir.test',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/masuk')->assertSessionHasErrors('email');
    expect(auth()->check())->toBeFalse();
});

it('mengizinkan pemilik masuk saat organisasi ditangguhkan', function () {
    $tenant = makeTenant(['status' => 'suspended']);
    $user = makeUser(['email' => 'pemilik@hadir.test']);
    makeMembership($tenant, $user, 'owner');

    $response = $this->post('/masuk', [
        'email' => 'pemilik@hadir.test',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/status');
    expect(auth()->id())->toBe($user->id);
});
