<?php

use App\Models\AttendanceDay;
use App\Models\AttendanceEvent;
use App\Services\SelfieService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function makeDayWithSelfie($tenant, $user, array $attributes = []): AttendanceDay
{
    Storage::disk('local')->put('selfies/uji.jpg', 'fake-jpeg-bytes');

    $day = AttendanceDay::create(array_merge([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'work_date' => '2026-10-05',
        'status' => 'hadir',
        'check_in_at' => now(),
    ], $attributes));

    AttendanceEvent::create([
        'tenant_id' => $tenant->id,
        'attendance_day_id' => $day->id,
        'user_id' => $user->id,
        'type' => 'check_in',
        'status' => 'accepted',
        'work_date' => '2026-10-05',
        'occurred_at' => now(),
        'selfie_path' => 'selfies/uji.jpg',
        'attempt_uuid' => (string) Str::uuid(),
    ]);

    return $day;
}

it('mengizinkan pemilik hari presensi melihat selfie lewat tautan bertanda tangan', function () {
    $tenant = makeTenant();
    $user = actingAsMember($tenant);
    $day = makeDayWithSelfie($tenant, $user);

    $url = app(SelfieService::class)->temporaryUrl($day->events()->first()->id);

    expect($url)->not->toBeNull();

    $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/jpeg');
});

it('menolak tautan selfie tanpa tanda tangan yang sah', function () {
    $tenant = makeTenant();
    $user = actingAsMember($tenant);
    $day = makeDayWithSelfie($tenant, $user);
    $event = $day->events()->first();

    $this->get('/selfie/'.$event->id)->assertForbidden();
});

it('menolak anggota lain melihat selfie rekan kerjanya', function () {
    $tenant = makeTenant();
    $owner = makeUser();
    makeMembership($tenant, $owner, 'owner');
    $member = makeUser();
    makeMembership($tenant, $member, 'member');

    $day = makeDayWithSelfie($tenant, $owner);
    $event = $day->events()->first();

    // Tautan dibuat atas nama pemilik, lalu dibuka oleh anggota lain.
    $url = app(SelfieService::class)->temporaryUrl($event->id);

    $this->actingAs($member)->get($url)->assertForbidden();
});

it('menolak platform admin membuka selfie', function () {
    $tenant = makeTenant();
    $owner = makeUser();
    makeMembership($tenant, $owner, 'owner');
    $day = makeDayWithSelfie($tenant, $owner);
    $event = $day->events()->first();

    $platform = makeUser(['is_platform_admin' => true]);
    $url = app(SelfieService::class)->temporaryUrl($event->id);

    $this->actingAs($platform)->get($url)->assertForbidden();
});

it('menolak selfie milik organisasi lain', function () {
    $tenantA = makeTenant(['name' => 'A']);
    $tenantB = makeTenant(['name' => 'B']);

    $ownerA = makeUser();
    makeMembership($tenantA, $ownerA, 'owner');
    $day = makeDayWithSelfie($tenantA, $ownerA);
    $event = $day->events()->first();

    $url = app(SelfieService::class)->temporaryUrl($event->id);

    $ownerB = makeUser();
    makeMembership($tenantB, $ownerB, 'owner');
    setTenant($tenantB);

    // Binding model memakai scope tenant, jadi kejadian organisasi lain tidak
    // ditemukan sama sekali. Yang penting responsnya bukan 200.
    $response = $this->actingAs($ownerB)->get($url);
    expect($response->status())->toBeIn([403, 404]);
});
