<?php

use App\Enums\MembershipRole;
use App\Models\Invitation;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Validation\ValidationException;

function inviteMember($tenant, $inviter, array $overrides = []): array
{
    return app(InvitationService::class)->create($tenant, array_merge([
        'email' => 'undangan@hadir.test',
        'role' => 'member',
    ], $overrides), $inviter);
}

it('membuat undangan dengan token sekali pakai', function () {
    $tenant = makeTenant();
    $owner = makeUser();
    makeMembership($tenant, $owner, 'owner');
    setTenant($tenant);

    [$invitation, $token] = inviteMember($tenant, $owner);

    expect($invitation->email)->toBe('undangan@hadir.test')
        ->and($invitation->token_hash)->toBe(hash('sha256', $token))
        ->and($invitation->expires_at->isAfter(now()->addDays(6)))->toBeTrue();
});

it('menampilkan halaman undangan untuk token yang sah', function () {
    $tenant = makeTenant();
    $owner = makeUser();
    makeMembership($tenant, $owner, 'owner');
    setTenant($tenant);

    [, $token] = inviteMember($tenant, $owner);

    $this->get('/undangan/'.$token)
        ->assertOk()
        ->assertSee('undangan@hadir.test');
});

it('menolak token kedaluwarsa', function () {
    $tenant = makeTenant();
    $owner = makeUser();
    makeMembership($tenant, $owner, 'owner');
    setTenant($tenant);

    [$invitation, $token] = inviteMember($tenant, $owner);
    $invitation->update(['expires_at' => now()->subDay()]);

    $this->get('/undangan/'.$token)->assertSee('kedaluwarsa', false);
});

it('menolak token yang sudah dipakai', function () {
    $tenant = makeTenant();
    $owner = makeUser();
    makeMembership($tenant, $owner, 'owner');
    setTenant($tenant);

    [, $token] = inviteMember($tenant, $owner);

    $this->post('/undangan/'.$token, [
        'name' => 'Anggota Baru',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertRedirect('/app');

    auth()->logout();

    $this->get('/undangan/'.$token)->assertSee('sudah diterima', false);
});

it('membuat akun dan keanggotaan saat undangan diterima', function () {
    $tenant = makeTenant();
    $owner = makeUser();
    makeMembership($tenant, $owner, 'owner');
    setTenant($tenant);

    [, $token] = inviteMember($tenant, $owner);

    $this->post('/undangan/'.$token, [
        'name' => 'Anggota Baru',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertRedirect('/app');

    $user = User::where('email', 'undangan@hadir.test')->firstOrFail();

    expect($user->memberships()->where('tenant_id', $tenant->id)->value('role')->value)->toBe('member')
        ->and(auth()->id())->toBe($user->id);
});

it('menolak undangan untuk pemilik', function () {
    $tenant = makeTenant();
    $owner = makeUser();
    makeMembership($tenant, $owner, 'owner');
    setTenant($tenant);

    expect(fn () => inviteMember($tenant, $owner, ['role' => 'owner']))
        ->toThrow(ValidationException::class);
});

it('menolak admin mengundang admin lain', function () {
    $tenant = makeTenant();
    $admin = makeUser();
    makeMembership($tenant, $admin, 'admin');
    setTenant($tenant);

    expect(fn () => inviteMember($tenant, $admin, ['role' => 'admin']))
        ->toThrow(ValidationException::class);
});

it('mewajibkan lokasi utama untuk peninjau', function () {
    $tenant = makeTenant();
    $owner = makeUser();
    makeMembership($tenant, $owner, 'owner');
    setTenant($tenant);

    expect(fn () => inviteMember($tenant, $owner, ['role' => 'reviewer']))
        ->toThrow(ValidationException::class);
});

it('menolak undangan ganda untuk email yang sama', function () {
    $tenant = makeTenant();
    $owner = makeUser();
    makeMembership($tenant, $owner, 'owner');
    setTenant($tenant);

    inviteMember($tenant, $owner);

    expect(fn () => inviteMember($tenant, $owner))
        ->toThrow(ValidationException::class);
});

it('menolak email yang sudah menjadi anggota', function () {
    $tenant = makeTenant();
    $owner = makeUser();
    makeMembership($tenant, $owner, 'owner');

    $existing = makeUser(['email' => 'sudah@hadir.test']);
    makeMembership($tenant, $existing, 'member');

    setTenant($tenant);

    expect(fn () => inviteMember($tenant, $owner, ['email' => 'sudah@hadir.test']))
        ->toThrow(ValidationException::class);
});

it('menolak tautan yang dicabut', function () {
    $tenant = makeTenant();
    $owner = makeUser();
    makeMembership($tenant, $owner, 'owner');
    setTenant($tenant);

    [$invitation, $token] = inviteMember($tenant, $owner);
    $invitation->update(['revoked_at' => now()]);

    $this->get('/undangan/'.$token)->assertSee('dicabut', false);
});

it('mewajibkan login bila email undangan sudah terdaftar', function () {
    $tenant = makeTenant();
    $owner = makeUser();
    makeMembership($tenant, $owner, 'owner');
    setTenant($tenant);

    makeUser(['email' => 'undangan@hadir.test']);

    [, $token] = inviteMember($tenant, $owner);

    $this->post('/undangan/'.$token, [
        'name' => 'Anggota Baru',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertRedirect('/masuk');
});

it('menghapus undangan yang kedaluwarsa lebih dari 30 hari lewat prune', function () {
    $tenant = makeTenant();
    $owner = actingAsAdmin($tenant);

    $old = Invitation::create([
        'tenant_id' => $tenant->id,
        'email' => 'lama@hadir.test',
        'role' => MembershipRole::Member,
        'token_hash' => hash('sha256', 'lama'),
        'invited_by_id' => $owner->id,
        'expires_at' => now()->subDays(45),
    ]);

    $recent = Invitation::create([
        'tenant_id' => $tenant->id,
        'email' => 'baru@hadir.test',
        'role' => MembershipRole::Member,
        'token_hash' => hash('sha256', 'baru'),
        'invited_by_id' => $owner->id,
        'expires_at' => now()->subDays(5),
    ]);

    $this->artisan('model:prune', ['--model' => [Invitation::class]])->assertSuccessful();

    expect(Invitation::withoutGlobalScopes()->find($old->id))->toBeNull();
    expect(Invitation::withoutGlobalScopes()->find($recent->id))->not->toBeNull();
});
