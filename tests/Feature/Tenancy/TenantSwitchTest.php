<?php

it('menolak anggota masuk ke organisasi ditangguhkan', function () {
    $tenant = makeTenant(['status' => 'suspended']);
    $user = makeUser();
    makeMembership($tenant, $user, 'member');

    $this->actingAs($user)->get('/app')->assertForbidden();
});

it('mengizinkan pemilik melihat halaman status organisasi ditangguhkan', function () {
    $tenant = makeTenant(['status' => 'suspended']);
    $user = makeUser();
    makeMembership($tenant, $user, 'owner');

    $this->actingAs($user)->get('/status')->assertOk();
});
