<?php

it('menolak anggota biasa membuka halaman pengelolaan', function (string $path) {
    $tenant = makeTenant();
    actingAsMember($tenant);

    $this->get($path)->assertForbidden();
})->with([
    '/app/anggota',
    '/app/laporan',
    '/app/pengaturan',
    '/app/lokasi',
    '/app/divisi',
    '/app/hari-libur',
]);

it('mengizinkan peninjau membuka tinjauan tetapi tidak pengaturan organisasi', function () {
    $tenant = makeTenant();
    $location = makeLocation($tenant);
    actingAsReviewer($tenant, $location);

    $this->get('/app/tinjauan')->assertOk();
    $this->get('/app/pengaturan')->assertForbidden();
    $this->get('/app/anggota')->assertForbidden();
});

it('mengizinkan admin membuka seluruh halaman pengelolaan', function (string $path) {
    $tenant = makeTenant();
    actingAsAdmin($tenant);

    $this->get($path)->assertOk();
})->with([
    '/app/anggota',
    '/app/pengaturan',
    '/app/lokasi',
    '/app/divisi',
    '/app/hari-libur',
]);

it('menampilkan halaman galat dalam bahasa Indonesia', function () {
    $response = $this->get('/halaman-yang-tidak-ada');

    $response->assertNotFound();
    $response->assertSee('Halaman tidak ditemukan');
    $response->assertSee('Ke beranda');
});
