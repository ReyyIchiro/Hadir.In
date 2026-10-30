<?php

use App\Enums\MembershipRole;
use App\Models\AttendanceDay;
use App\Models\Division;
use App\Models\Holiday;
use App\Models\Invitation;
use App\Models\Location;
use App\Models\Membership;
use App\Models\Schedule;

it('menyaring setiap model tenant berdasarkan konteks aktif', function () {
    $tenantA = makeTenant(['name' => 'Tenant A']);
    $tenantB = makeTenant(['name' => 'Tenant B']);

    $userA = makeUser();
    $userB = makeUser();

    makeMembership($tenantA, $userA, 'owner');
    makeMembership($tenantB, $userB, 'owner');

    makeLocation($tenantA, ['name' => 'Lokasi A']);
    makeLocation($tenantB, ['name' => 'Lokasi B']);

    makeDivision($tenantA);
    makeDivision($tenantB);

    makeSchedule($tenantA, $userA, '2026-10-01');
    makeSchedule($tenantB, $userB, '2026-10-01');

    Holiday::create(['tenant_id' => $tenantA->id, 'date' => '2026-10-05', 'name' => 'Libur A']);
    Holiday::create(['tenant_id' => $tenantB->id, 'date' => '2026-10-05', 'name' => 'Libur B']);

    AttendanceDay::create([
        'tenant_id' => $tenantA->id,
        'user_id' => $userA->id,
        'work_date' => '2026-10-01',
        'status' => 'hadir',
        'check_in_at' => now(),
    ]);

    AttendanceDay::create([
        'tenant_id' => $tenantB->id,
        'user_id' => $userB->id,
        'work_date' => '2026-10-01',
        'status' => 'hadir',
        'check_in_at' => now(),
    ]);

    Invitation::create([
        'tenant_id' => $tenantA->id,
        'email' => 'a@hadir.test',
        'role' => MembershipRole::Member,
        'token_hash' => hash('sha256', 'a'),
        'invited_by_id' => $userA->id,
        'expires_at' => now()->addDays(7),
    ]);

    Invitation::create([
        'tenant_id' => $tenantB->id,
        'email' => 'b@hadir.test',
        'role' => MembershipRole::Member,
        'token_hash' => hash('sha256', 'b'),
        'invited_by_id' => $userB->id,
        'expires_at' => now()->addDays(7),
    ]);

    setTenant($tenantA);

    expect(Location::count())->toBe(1)
        ->and(Location::first()->name)->toBe('Lokasi A')
        ->and(Division::count())->toBe(1)
        ->and(Schedule::count())->toBe(1)
        ->and(Holiday::count())->toBe(1)
        ->and(AttendanceDay::count())->toBe(1)
        ->and(Invitation::count())->toBe(1)
        ->and(Membership::count())->toBe(1);
});

it('mengembalikan 404 saat membuka data tenant lain lewat route model binding', function () {
    $tenantA = makeTenant(['name' => 'Tenant A']);
    $tenantB = makeTenant(['name' => 'Tenant B']);

    $userA = makeUser();
    makeMembership($tenantA, $userA, 'owner');

    $userB = makeUser();
    makeMembership($tenantB, $userB, 'owner');

    $dayB = AttendanceDay::withoutGlobalScopes()->create([
        'tenant_id' => $tenantB->id,
        'user_id' => $userB->id,
        'work_date' => '2026-10-01',
        'status' => 'hadir',
        'check_in_at' => now(),
    ]);

    $this->actingAs($userA)->get('/app/presensi/'.$dayB->id)->assertNotFound();
});

it('menolak pindah ke organisasi tanpa keanggotaan', function () {
    $tenantA = makeTenant();
    $tenantB = makeTenant();

    $user = makeUser();
    makeMembership($tenantA, $user, 'owner');

    $this->actingAs($user)
        ->post('/app/organisasi/'.$tenantB->id.'/pilih')
        ->assertForbidden();
});

it('menolak platform admin membuka area aplikasi', function () {
    $user = makeUser(['is_platform_admin' => true]);

    $this->actingAs($user)->get('/app')->assertForbidden();
});

it('mengizinkan pemilik berpindah ke organisasi lain yang diikuti', function () {
    $tenantA = makeTenant();
    $tenantB = makeTenant();

    $user = makeUser();
    makeMembership($tenantA, $user, 'owner');
    makeMembership($tenantB, $user, 'admin');

    $this->actingAs($user)
        ->post('/app/organisasi/'.$tenantB->id.'/pilih')
        ->assertRedirect();

    expect(session('active_tenant_id'))->toBe($tenantB->id);
});
