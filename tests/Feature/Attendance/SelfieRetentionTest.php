<?php

use App\Models\AttendanceDay;
use App\Models\AttendanceEvent;
use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function makeRetentionEvent(Tenant $tenant, int $userId, string $occurredAt, string $path): AttendanceEvent
{
    Storage::disk('local')->put($path, 'jpeg');

    $day = AttendanceDay::create([
        'tenant_id' => $tenant->id,
        'user_id' => $userId,
        'work_date' => substr($occurredAt, 0, 10),
        'status' => 'hadir',
        'check_in_at' => $occurredAt,
    ]);

    return AttendanceEvent::create([
        'tenant_id' => $tenant->id,
        'attendance_day_id' => $day->id,
        'user_id' => $userId,
        'type' => 'check_in',
        'status' => 'accepted',
        'work_date' => substr($occurredAt, 0, 10),
        'occurred_at' => $occurredAt,
        'selfie_path' => $path,
        'attempt_uuid' => (string) Str::uuid(),
    ]);
}

it('menghapus berkas selfie yang melewati masa retensi', function () {
    $tenant = makeTenant();
    $user = makeUser();
    makeMembership($tenant, $user, 'owner');
    $tenant->settings()->update(['selfie_retention_days' => 90]);

    $old = makeRetentionEvent($tenant, $user->id, now()->subDays(120)->toDateTimeString(), 'selfies/lama.jpg');

    $this->artisan('selfies:purge')->assertSuccessful();

    expect(Storage::disk('local')->exists('selfies/lama.jpg'))->toBeFalse();
    expect($old->refresh()->selfie_path)->toBeNull();
    // Catatan presensi tetap ada, hanya berkasnya yang hilang.
    expect(AttendanceEvent::withoutGlobalScopes()->find($old->id))->not->toBeNull();
});

it('menyimpan selfie yang belum melewati masa retensi', function () {
    $tenant = makeTenant();
    $user = makeUser();
    makeMembership($tenant, $user, 'owner');
    $tenant->settings()->update(['selfie_retention_days' => 90]);

    $fresh = makeRetentionEvent($tenant, $user->id, now()->subDays(10)->toDateTimeString(), 'selfies/baru.jpg');

    $this->artisan('selfies:purge')->assertSuccessful();

    expect(Storage::disk('local')->exists('selfies/baru.jpg'))->toBeTrue();
    expect($fresh->refresh()->selfie_path)->toBe('selfies/baru.jpg');
});

it('menghormati masa retensi khusus per organisasi', function () {
    $tenant = makeTenant();
    $user = makeUser();
    makeMembership($tenant, $user, 'owner');
    $tenant->settings()->update(['selfie_retention_days' => 7]);

    $event = makeRetentionEvent($tenant, $user->id, now()->subDays(30)->toDateTimeString(), 'selfies/pendek.jpg');

    $this->artisan('selfies:purge')->assertSuccessful();

    expect($event->refresh()->selfie_path)->toBeNull();
});
