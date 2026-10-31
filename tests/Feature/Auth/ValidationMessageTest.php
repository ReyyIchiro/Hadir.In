<?php

function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Rani Prameswari',
        'email' => 'rani@hadir.test',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'organization' => 'Toko Rani',
        'timezone' => 'Asia/Jakarta',
    ], $overrides);
}

it('menjelaskan kata sandi tanpa huruf dalam bahasa Indonesia', function () {
    $response = $this->from('/daftar')->post('/daftar', registrationPayload([
        'password' => '12345678',
        'password_confirmation' => '12345678',
    ]));

    $response->assertRedirect('/daftar')
        ->assertSessionHasErrors(['password' => 'Kolom kata sandi harus memuat minimal satu huruf.']);
});

it('menjelaskan kata sandi tanpa angka dalam bahasa Indonesia', function () {
    $response = $this->from('/daftar')->post('/daftar', registrationPayload([
        'password' => 'rahasiaku',
        'password_confirmation' => 'rahasiaku',
    ]));

    $response->assertRedirect('/daftar')
        ->assertSessionHasErrors(['password' => 'Kolom kata sandi harus memuat minimal satu angka.']);
});

it('menjelaskan kata sandi yang terlalu pendek dalam bahasa Indonesia', function () {
    $response = $this->from('/daftar')->post('/daftar', registrationPayload([
        'password' => 'rahas1',
        'password_confirmation' => 'rahas1',
    ]));

    $response->assertRedirect('/daftar')
        ->assertSessionHasErrors(['password' => 'Kolom kata sandi minimal 8 karakter.']);
});

it('menampilkan galat pendaftaran tanpa kunci terjemahan mentah', function () {
    $this->from('/daftar')->post('/daftar', [])
        ->assertSessionHasErrors([
            'name' => 'Kolom nama wajib diisi.',
            'email' => 'Kolom alamat email wajib diisi.',
            'password' => 'Kolom kata sandi wajib diisi.',
        ]);

    foreach (session('errors')->getBag('default')->all() as $message) {
        expect($message)->not->toContain('validation.');
    }
});

it('menampilkan galat masuk tanpa kunci terjemahan mentah', function () {
    $this->from('/masuk')->post('/masuk', [])
        ->assertSessionHasErrors([
            'email' => 'Kolom alamat email wajib diisi.',
            'password' => 'Kolom kata sandi wajib diisi.',
        ]);

    foreach (session('errors')->getBag('default')->all() as $message) {
        expect($message)->not->toContain('validation.');
    }
});

it('menampilkan semua aturan kata sandi yang dilanggar sekaligus', function () {
    $this->followingRedirects()
        ->from('/daftar')
        ->post('/daftar', registrationPayload([
            'password' => '123',
            'password_confirmation' => '123',
        ]))
        ->assertOk()
        ->assertSee('Kolom kata sandi minimal 8 karakter.')
        ->assertSee('Kolom kata sandi harus memuat minimal satu huruf.')
        ->assertDontSee('validation.password');
});
