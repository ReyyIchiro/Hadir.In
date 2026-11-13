<?php

use App\Models\Tenant;

it('mengarahkan anggota ke halaman status saat organisasi ditangguhkan', function () {
    $tenant = makeTenant(['status' => 'suspended']);
    $user = makeUser();
    makeMembership($tenant, $user, 'member');

    // Akses data tetap terblokir, tetapi melalui halaman status, bukan 403,
    // supaya pengguna tetap bisa berpindah ke organisasi lainnya.
    $this->actingAs($user)->get('/app')->assertRedirect('/status');
});

it('mengarahkan anggota ke halaman status saat organisasi belum aktif', function () {
    $tenant = makeTenant(['status' => 'pending']);
    $user = makeUser();
    makeMembership($tenant, $user, 'member');

    $this->actingAs($user)->get('/app')->assertRedirect('/status');
});

it('mengizinkan pemilik melihat halaman status organisasi ditangguhkan', function () {
    $tenant = makeTenant(['status' => 'suspended']);
    $user = makeUser();
    makeMembership($tenant, $user, 'owner');

    $this->actingAs($user)->get('/status')->assertOk();
});

it('memungkinkan anggota pindah ke organisasi aktif dari organisasi ditangguhkan', function () {
    $suspended = makeTenant(['status' => 'suspended', 'slug' => 'uji-tangguhkan']);
    $active = makeTenant(['slug' => 'uji-aktif']);
    $user = makeUser();
    makeMembership($suspended, $user, 'member');
    makeMembership($active, $user, 'member');

    $this->actingAs($user);

    // Halaman status memperlihatkan organisasi lain yang aktif.
    $this->get('/status')
        ->assertOk()
        ->assertSee($active->name);

    $this->post('/app/organisasi/'.$active->id.'/pilih')
        ->assertRedirect('/app');

    expect((int) session('active_tenant_id'))->toBe($active->id);

    // Aplikasi kini memakai organisasi aktif, bukan yang ditangguhkan.
    $this->get('/app')->assertOk();
});

it('menolak pindah ke organisasi yang tidak diikuti', function () {
    $other = makeTenant(['slug' => 'organisasi-asing']);
    $active = makeTenant(['slug' => 'organisasi-sendiri']);
    $user = makeUser();
    makeMembership($active, $user, 'member');

    $this->actingAs($user)
        ->post('/app/organisasi/'.$other->id.'/pilih')
        ->assertForbidden();

    expect(Tenant::count())->toBe(2);
});
