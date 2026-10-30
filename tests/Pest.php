<?php

use App\Enums\MembershipRole;
use App\Enums\TenantStatus;
use App\Models\Division;
use App\Models\Location;
use App\Models\Membership;
use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test case
|--------------------------------------------------------------------------
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function makeTenant(array $attributes = []): Tenant
{
    $tenant = Tenant::create(array_merge([
        'name' => 'Organisasi Uji',
        'slug' => 'organisasi-uji-'.Str::lower(Str::random(6)),
        'timezone' => 'Asia/Jakarta',
        'status' => TenantStatus::Active,
        'plan' => 'standard',
    ], $attributes));

    TenantSetting::create(array_merge([
        'tenant_id' => $tenant->id,
    ], $attributes['settings'] ?? []));

    return $tenant->refresh();
}

function makeUser(array $attributes = []): User
{
    return User::create(array_merge([
        'name' => 'Pengguna Uji',
        'email' => 'pengguna-'.Str::lower(Str::random(8)).'@hadir.test',
        'password' => 'password123',
    ], $attributes));
}

function makeMembership(Tenant $tenant, User $user, string $role = 'member', array $attributes = []): Membership
{
    return Membership::create(array_merge([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'role' => $role,
        'is_active' => true,
        'joined_at' => now(),
    ], $attributes));
}

function makeLocation(Tenant $tenant, array $attributes = []): Location
{
    return Location::create(array_merge([
        'tenant_id' => $tenant->id,
        'name' => 'Kantor Uji',
        'address' => 'Jalan Uji 1',
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'radius_meters' => 50,
        'is_active' => true,
    ], $attributes));
}

function makeDivision(Tenant $tenant, array $attributes = []): Division
{
    return Division::create(array_merge([
        'tenant_id' => $tenant->id,
        'name' => 'Divisi Uji',
        'is_active' => true,
    ], $attributes));
}

function makeSchedule(Tenant $tenant, User $user, string $date, string $start = '08:00', string $end = '17:00'): Schedule
{
    return Schedule::create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'work_date' => $date,
        'is_day_off' => false,
        'start_time' => $start.':00',
        'end_time' => $end.':00',
    ]);
}

function actingAsMember(Tenant $tenant, array $attributes = []): User
{
    $user = makeUser($attributes);
    makeMembership($tenant, $user, MembershipRole::Member->value);

    test()->actingAs($user);
    setTenant($tenant);

    return $user;
}

function actingAsAdmin(Tenant $tenant, array $attributes = []): User
{
    $user = makeUser($attributes);
    makeMembership($tenant, $user, MembershipRole::Admin->value);

    test()->actingAs($user);
    setTenant($tenant);

    return $user;
}

function actingAsReviewer(Tenant $tenant, Location $location, array $attributes = []): User
{
    $user = makeUser($attributes);
    makeMembership($tenant, $user, MembershipRole::Reviewer->value, [
        'primary_location_id' => $location->id,
    ]);

    test()->actingAs($user);
    setTenant($tenant);

    return $user;
}

function setTenant(?Tenant $tenant): void
{
    $context = app(TenantContext::class);
    $context->clear();

    if ($tenant === null) {
        return;
    }

    // Utamakan keanggotaan milik pengguna yang sedang login. Tanpa ini,
    // setTenant() bisa memilih keanggotaan orang lain dan uji kebijakan
    // lolos atau gagal karena alasan yang salah.
    $membership = null;
    $currentId = auth()->id();

    if ($currentId !== null) {
        $membership = Membership::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $currentId)
            ->where('is_active', true)
            ->first();
    }

    $membership ??= Membership::withoutGlobalScopes()
        ->where('tenant_id', $tenant->id)
        ->where('is_active', true)
        ->first();

    if ($membership === null) {
        $user = makeUser();
        $membership = makeMembership($tenant, $user, MembershipRole::Member->value);
    }

    $context->set($tenant, $membership);
}

function selfieFile(): UploadedFile
{
    $image = imagecreatetruecolor(40, 40);
    imagefill($image, 0, 0, imagecolorallocate($image, 200, 120, 90));
    ob_start();
    imagejpeg($image, null, 70);
    $bytes = ob_get_clean();
    imagedestroy($image);

    return UploadedFile::fake()->createWithContent('selfie.jpg', $bytes);
}

function freezeAt(string $utc): CarbonImmutable
{
    $moment = CarbonImmutable::parse($utc, 'UTC');
    CarbonImmutable::setTestNow($moment);
    Carbon\Carbon::setTestNow($moment);

    return $moment;
}
