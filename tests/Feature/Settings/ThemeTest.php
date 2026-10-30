<?php

beforeEach(function () {
    $this->tenant = makeTenant();
    $this->owner = makeUser();
    makeMembership($this->tenant, $this->owner, 'owner');
    setTenant($this->tenant);
});

it('memuat bootstrap tema sebelum stylesheet supaya tidak ada kedipan', function () {
    $html = $this->actingAs($this->owner)->get('/app/ringkasan')->assertOk()->getContent();

    $themeScript = strpos($html, "localStorage.getItem('hadir-theme')");
    $stylesheet = strpos($html, 'rel="stylesheet"');

    expect($themeScript)->not->toBeFalse()
        ->and($stylesheet)->not->toBeFalse()
        ->and($themeScript)->toBeLessThan($stylesheet);
});

it('menyediakan tombol tema di layout aplikasi dan publik', function () {
    $app = $this->actingAs($this->owner)->get('/app/ringkasan')->assertOk()->getContent();
    expect($app)->toContain('data-theme-toggle');

    auth()->logout();

    $public = $this->get('/')->assertOk()->getContent();
    expect($public)->toContain('data-theme-toggle');
});

it('menyediakan tombol tema di halaman autentikasi', function () {
    $auth = $this->get('/masuk')->assertOk()->getContent();

    expect($auth)->toContain('data-theme-toggle');
});

it('menyediakan meta theme-color yang diperbarui skrip tema', function () {
    $html = $this->actingAs($this->owner)->get('/app/ringkasan')->assertOk()->getContent();

    expect($html)->toContain('data-theme-color');
});

it('menampilkan halaman tampilan dengan tiga pilihan tema', function () {
    $html = $this->actingAs($this->owner)->get('/app/tampilan')->assertOk()->getContent();

    expect($html)->toContain('Ikuti sistem')
        ->and($html)->toContain('Terang')
        ->and($html)->toContain('Gelap');
});

it('memakai satu sumber logika tema, bukan duplikat di tiap layout', function () {
    $layouts = glob(resource_path('views/components/layouts/*.blade.php'));

    expect($layouts)->not->toBeEmpty();

    foreach ($layouts as $layout) {
        $contents = file_get_contents($layout);

        expect(substr_count($contents, 'partials.theme'))->toBe(1);
        expect(substr_count($contents, "localStorage.getItem('hadir-theme')"))->toBe(0);
    }
});
