<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    $this->user = makeUser(['email' => 'reset@hadir.test']);
});

function resetToken(User $user): string
{
    $token = null;

    Notification::assertSentTo(
        $user,
        ResetPassword::class,
        function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        }
    );

    return $token;
}

it('mengirim tautan atur ulang ke email terdaftar', function () {
    $this->post('/lupa-kata-sandi', ['email' => 'reset@hadir.test'])
        ->assertRedirect()
        ->assertSessionHas('status');

    Notification::assertSentTo($this->user, ResetPassword::class);
});

it('tidak membocorkan keberadaan email pada tautan atur ulang', function () {
    $this->post('/lupa-kata-sandi', ['email' => 'tidak-ada@hadir.test'])
        ->assertRedirect()
        ->assertSessionHas('status');

    Notification::assertNothingSent();
});

it('mengatur ulang kata sandi dengan token yang sah', function () {
    $this->post('/lupa-kata-sandi', ['email' => 'reset@hadir.test']);
    $token = resetToken($this->user);

    $this->get('/atur-ulang-kata-sandi/'.$token)->assertOk();

    $this->post('/atur-ulang-kata-sandi', [
        'token' => $token,
        'email' => 'reset@hadir.test',
        'password' => 'sandibaru123',
        'password_confirmation' => 'sandibaru123',
    ])->assertRedirect('/masuk')->assertSessionHas('status');

    expect(Auth::attempt(['email' => 'reset@hadir.test', 'password' => 'sandibaru123']))->toBeTrue();
});

it('menolak token yang sudah dipakai', function () {
    $this->post('/lupa-kata-sandi', ['email' => 'reset@hadir.test']);
    $token = resetToken($this->user);

    $this->post('/atur-ulang-kata-sandi', [
        'token' => $token,
        'email' => 'reset@hadir.test',
        'password' => 'sandibaru123',
        'password_confirmation' => 'sandibaru123',
    ])->assertRedirect('/masuk');

    Notification::fake();

    $this->from('/atur-ulang-kata-sandi/'.$token)->post('/atur-ulang-kata-sandi', [
        'token' => $token,
        'email' => 'reset@hadir.test',
        'password' => 'sandibaru456',
        'password_confirmation' => 'sandibaru456',
    ])->assertRedirect('/atur-ulang-kata-sandi/'.$token)->assertSessionHasErrors('email');
});

it('menolak kata sandi tanpa angka atau huruf', function () {
    $this->post('/lupa-kata-sandi', ['email' => 'reset@hadir.test']);
    $token = resetToken($this->user);

    $this->from('/atur-ulang-kata-sandi/'.$token)->post('/atur-ulang-kata-sandi', [
        'token' => $token,
        'email' => 'reset@hadir.test',
        'password' => '12345678',
        'password_confirmation' => '12345678',
    ])->assertSessionHasErrors('password');
});

it('memutus sesi lain setelah kata sandi diatur ulang', function () {
    $this->post('/lupa-kata-sandi', ['email' => 'reset@hadir.test']);
    $token = resetToken($this->user);

    $this->post('/atur-ulang-kata-sandi', [
        'token' => $token,
        'email' => 'reset@hadir.test',
        'password' => 'sandibaru123',
        'password_confirmation' => 'sandibaru123',
    ])->assertRedirect('/masuk');

    // Penanda sesi password_hash_web diubah, jadi sesi yang tidak menyimpan
    // penanda kata sandi terbaru akan keluar saat permintaan berikutnya.
    expect(Auth::attempt(['email' => 'reset@hadir.test', 'password' => 'sandibaru123']))->toBeTrue()
        ->and($this->user->fresh()->password)->not->toBeNull();
});
