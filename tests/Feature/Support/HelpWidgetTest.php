<?php

it('menampilkan tombol bantuan WhatsApp di halaman publik', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('https://wa.me/6281399936900', false);
    $response->assertSee('Butuh bantuan?');
});

it('menampilkan tombol bantuan di halaman masuk', function () {
    $response = $this->get('/masuk');

    $response->assertOk();
    $response->assertSee('https://wa.me/6281399936900', false);
});

it('tidak menampilkan tombol bantuan di dalam aplikasi', function () {
    $tenant = makeTenant();
    actingAsMember($tenant);

    $response = $this->get('/app');

    $response->assertOk();
    $response->assertDontSee('https://wa.me/', false);
});

it('menyembunyikan tombol bantuan bila nomor kosong', function () {
    config(['services.support.whatsapp' => '']);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertDontSee('https://wa.me/', false);
});

it('menyusun pesan bantuan dengan nama, organisasi, dan kendala', function () {
    $response = $this->get('/');

    $response->assertSee(
        'text='.rawurlencode("Halo Hadir.In, saya butuh bantuan.\n\nNama:\nOrganisasi:\nYang ingin ditanyakan:\n"),
        false
    );
});
