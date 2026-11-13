<?php

use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    $this->tenant = makeTenant();
    $this->user = makeUser(['email' => 'saya@hadir.test']);
    makeMembership($this->tenant, $this->user, 'member');
    setTenant($this->tenant);
});

it('memperbarui nama tanpa mengubah email', function () {
    $this->actingAs($this->user);

    Livewire::test(Profile::class)
        ->set('name', 'Nama Baru')
        ->set('email', 'saya@hadir.test')
        ->call('save')
        ->assertHasNoErrors();

    expect($this->user->fresh()->name)->toBe('Nama Baru')
        ->and($this->user->fresh()->email)->toBe('saya@hadir.test');
});

it('mewajibkan kata sandi saat ini untuk mengubah email', function () {
    $this->actingAs($this->user);

    Livewire::test(Profile::class)
        ->set('email', 'email-baru@hadir.test')
        ->call('save')
        ->assertHasErrors('current_password');

    expect($this->user->fresh()->email)->toBe('saya@hadir.test');
});

it('menolak perubahan email dengan kata sandi yang salah', function () {
    $this->actingAs($this->user);

    Livewire::test(Profile::class)
        ->set('email', 'email-baru@hadir.test')
        ->set('current_password', 'bukan-sandi')
        ->call('save')
        ->assertHasErrors('current_password');

    expect($this->user->fresh()->email)->toBe('saya@hadir.test');
});

it('mengubah email dengan kata sandi yang sah', function () {
    $this->actingAs($this->user);

    Livewire::test(Profile::class)
        ->set('email', 'Email-Baru@hadir.test')
        ->set('current_password', 'password123')
        ->call('save')
        ->assertHasNoErrors();

    // Email dinormalisasi ke huruf kecil sebelum disimpan.
    expect($this->user->fresh()->email)->toBe('email-baru@hadir.test');
});

it('mengubah kata sandi dan memutus sesi lain', function () {
    $this->actingAs($this->user);

    Livewire::test(Profile::class)
        ->set('current_password', 'password123')
        ->set('password', 'sandibaru123')
        ->set('password_confirmation', 'sandibaru123')
        ->call('updatePassword')
        ->assertHasNoErrors();

    $user = $this->user->fresh();

    expect(Hash::check('sandibaru123', $user->password))->toBeTrue()
        ->and(Auth::attempt(['email' => 'saya@hadir.test', 'password' => 'sandibaru123']))->toBeTrue();
});

it('menolak kata sandi baru dengan sandi saat ini yang salah', function () {
    $this->actingAs($this->user);

    Livewire::test(Profile::class)
        ->set('current_password', 'bukan-sandi')
        ->set('password', 'sandibaru123')
        ->set('password_confirmation', 'sandibaru123')
        ->call('updatePassword')
        ->assertHasErrors('current_password');

    expect(Hash::check('password123', $this->user->fresh()->password))->toBeTrue();
});
