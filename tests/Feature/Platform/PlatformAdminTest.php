<?php

use App\Models\AttendanceDay;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->tenant = makeTenant();
    $this->location = makeLocation($this->tenant);
    $this->owner = makeUser();
    makeMembership($this->tenant, $this->owner, 'owner');
    $this->platform = makeUser(['is_platform_admin' => true]);
    setTenant(null);
});

it('menolak pengguna biasa membuka dasbor platform', function () {
    $this->actingAs($this->owner)
        ->get('/platform')
        ->assertForbidden();
});

it('mengizinkan admin platform membuka dasbor', function () {
    $this->actingAs($this->platform)
        ->get('/platform')
        ->assertOk();
});

it('menampilkan daftar organisasi untuk admin platform', function () {
    $this->actingAs($this->platform)
        ->get('/platform/tenant')
        ->assertOk()
        ->assertSee($this->tenant->name);
});

it('mengaktifkan organisasi yang menunggu', function () {
    $this->tenant->update(['status' => 'pending']);

    $this->actingAs($this->platform)
        ->post('/platform/tenant/'.$this->tenant->id.'/aktifkan')
        ->assertRedirect();

    expect($this->tenant->refresh()->status->value)->toBe('active');
});

it('menangguhkan organisasi aktif', function () {
    $this->tenant->update(['status' => 'active']);

    $this->actingAs($this->platform)
        ->post('/platform/tenant/'.$this->tenant->id.'/tangguhkan')
        ->assertRedirect();

    expect($this->tenant->refresh()->status->value)->toBe('suspended');
});

it('mengaktifkan kembali organisasi yang ditangguhkan', function () {
    $this->tenant->update(['status' => 'suspended']);

    $this->actingAs($this->platform)
        ->post('/platform/tenant/'.$this->tenant->id.'/aktifkan-kembali')
        ->assertRedirect();

    expect($this->tenant->refresh()->status->value)->toBe('active');
});

it('tidak menampilkan data presensi pada halaman organisasi', function () {
    $member = makeUser();
    makeMembership($this->tenant, $member, 'member', ['primary_location_id' => $this->location->id]);

    AttendanceDay::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $member->id,
        'work_date' => '2026-10-30',
        'status' => 'hadir',
        'check_in_at' => '2026-10-30 00:55:00',
    ]);

    $this->actingAs($this->platform)
        ->get('/platform/tenant/'.$this->tenant->id)
        ->assertOk()
        ->assertDontSee($member->email);
});

it('menolak admin platform mengakses rute aplikasi tenant', function () {
    $this->actingAs($this->platform)
        ->get('/app')
        ->assertForbidden();
});

it('menolak admin platform membuka beranda anggota tanpa keanggotaan', function () {
    $this->actingAs($this->platform)
        ->get('/app/presensi')
        ->assertForbidden();
});

it('menolak pemilik menangguhkan organisasinya sendiri', function () {
    $this->actingAs($this->owner)
        ->post('/platform/tenant/'.$this->tenant->id.'/tangguhkan')
        ->assertForbidden();
});
