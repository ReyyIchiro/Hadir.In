<?php

use App\Livewire\Divisions\Index;
use App\Livewire\Locations\Form;
use App\Livewire\Members\Edit;
use App\Models\Division;
use App\Models\Holiday;
use App\Models\Location;
use App\Models\Membership;
use App\Models\Schedule;
use App\Models\TenantSetting;
use App\Services\ScheduleService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Storage::fake('local');
    $this->tenant = makeTenant();
    $this->owner = makeUser();
    makeMembership($this->tenant, $this->owner, 'owner');
    $this->member = makeUser();
    makeMembership($this->tenant, $this->member, 'member');
    setTenant($this->tenant);
});

it('menampilkan halaman pengaturan organisasi untuk admin', function () {
    $this->actingAs($this->owner)->get('/app/pengaturan')->assertOk();
});

it('menolak anggota membuka pengaturan organisasi', function () {
    $this->actingAs($this->member)->get('/app/pengaturan')->assertForbidden();
});

it('menolak anggota membuka formulir lokasi', function () {
    $this->actingAs($this->member)
        ->get('/app/lokasi/baru')
        ->assertForbidden();
});

it('membuat lokasi baru lewat komponen', function () {
    $this->actingAs($this->owner);

    Livewire\Livewire::test(Form::class)
        ->set('name', 'Kantor Pusat')
        ->set('latitude', '-6.2000000')
        ->set('longitude', '106.8166667')
        ->set('radius_meters', '50')
        ->set('is_active', true)
        ->call('save')
        ->assertHasNoErrors();

    $location = Location::firstOrFail();

    expect($location->name)->toBe('Kantor Pusat')
        ->and($location->tenant_id)->toBe($this->tenant->id)
        ->and($location->radius_meters)->toBe(50);
});

it('menolak lokasi dengan radius di luar batas', function () {
    $this->actingAs($this->owner);

    Livewire\Livewire::test(Form::class)
        ->set('name', 'Terlalu Luas')
        ->set('latitude', '-6.2')
        ->set('longitude', '106.8')
        ->set('radius_meters', '6000')
        ->call('save')
        ->assertHasErrors('radius_meters');

    expect(Location::count())->toBe(0);
});

it('menolak lokasi tanpa nama', function () {
    $this->actingAs($this->owner);

    Livewire\Livewire::test(Form::class)
        ->set('name', '')
        ->set('latitude', '-6.2')
        ->set('longitude', '106.8')
        ->set('radius_meters', '50')
        ->call('save')
        ->assertHasErrors('name');
});

it('membuat divisi baru', function () {
    $this->actingAs($this->owner);

    Livewire\Livewire::test(Index::class)
        ->set('name', 'Gudang')
        ->call('save')
        ->assertHasNoErrors();

    expect(Division::firstOrFail()->name)->toBe('Gudang');
});

it('menolak nama divisi ganda dalam satu tenant', function () {
    Division::create(['tenant_id' => $this->tenant->id, 'name' => 'Gudang', 'is_active' => true]);

    $this->actingAs($this->owner);

    Livewire\Livewire::test(Index::class)
        ->set('name', 'Gudang')
        ->call('save')
        ->assertHasErrors('name');
});

it('menyimpan pengaturan presensi', function () {
    $this->actingAs($this->owner);

    Livewire\Livewire::test(App\Livewire\Settings\Index::class)
        ->set('work_start', '09:00')
        ->set('work_end', '18:00')
        ->set('early_window_minutes', '45')
        ->set('grace_minutes', '15')
        ->set('checkout_late_minutes', '180')
        ->set('early_leave_minutes', '15')
        ->set('geofence_buffer_meters', '40')
        ->set('low_accuracy_meters', '120')
        ->set('gps_timeout_seconds', '20')
        ->set('selfie_retention_days', '60')
        ->call('save')
        ->assertHasNoErrors();

    $settings = TenantSetting::firstOrFail();

    expect($settings->work_start)->toBe('09:00:00')
        ->and($settings->grace_minutes)->toBe(15)
        ->and($settings->geofence_buffer_meters)->toBe(40);
});

it('menerapkan jadwal massal ke banyak anggota', function () {
    $this->actingAs($this->owner);

    $service = app(ScheduleService::class);

    $counts = $service->bulkApply($this->tenant, [
        'user_ids' => [$this->owner->id, $this->member->id],
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-08',
        'weekdays' => [1, 2, 3, 4, 5],
        'start_time' => '08:00',
        'end_time' => '17:00',
        'mode' => 'skip',
    ]);

    // Lima hari kerja untuk dua anggota.
    expect($counts['created'])->toBe(10)
        ->and(Schedule::count())->toBe(10);
});

it('melewati jadwal yang sudah ada saat mode skip', function () {
    $this->actingAs($this->owner);

    $service = app(ScheduleService::class);
    $payload = [
        'user_ids' => [$this->member->id],
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-02',
        'weekdays' => [1],
        'start_time' => '08:00',
        'end_time' => '17:00',
        'mode' => 'skip',
    ];

    $service->bulkApply($this->tenant, $payload);
    $counts = $service->bulkApply($this->tenant, $payload);

    expect($counts['skipped'])->toBe(1)
        ->and($counts['created'])->toBe(0);
});

it('menimpa jadwal yang sudah ada saat mode overwrite', function () {
    $service = app(ScheduleService::class);
    $payload = [
        'user_ids' => [$this->member->id],
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-02',
        'weekdays' => [1],
        'start_time' => '08:00',
        'end_time' => '17:00',
        'mode' => 'overwrite',
    ];

    $service->bulkApply($this->tenant, $payload);

    $counts = $service->bulkApply($this->tenant, array_merge($payload, [
        'start_time' => '09:00',
        'end_time' => '18:00',
    ]));

    expect($counts['updated'])->toBe(1)
        ->and(substr((string) Schedule::firstOrFail()->start_time, 0, 5))->toBe('09:00');
});

it('menolak penerapan massal tanpa hari kerja', function () {
    $service = app(ScheduleService::class);

    expect(fn () => $service->bulkApply($this->tenant, [
        'user_ids' => [$this->member->id],
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-08',
        'weekdays' => [],
        'start_time' => '08:00',
        'end_time' => '17:00',
    ]))->toThrow(ValidationException::class);
});

it('menolak penerapan massal lebih dari 92 hari', function () {
    $service = app(ScheduleService::class);

    expect(fn () => $service->bulkApply($this->tenant, [
        'user_ids' => [$this->member->id],
        'start_date' => '2026-11-01',
        'end_date' => '2027-03-01',
        'weekdays' => [1],
        'start_time' => '08:00',
        'end_time' => '17:00',
    ]))->toThrow(ValidationException::class);
});

it('membuat hari libur baru', function () {
    $this->actingAs($this->owner);

    Livewire\Livewire::test(App\Livewire\Holidays\Index::class)
        ->set('date', '2026-12-25')
        ->set('name', 'Natal')
        ->call('save')
        ->assertHasNoErrors();

    expect(Holiday::firstOrFail()->name)->toBe('Natal');
});

it('menolak hari libur ganda pada tanggal yang sama', function () {
    Holiday::create([
        'tenant_id' => $this->tenant->id,
        'date' => '2026-12-25',
        'name' => 'Natal',
    ]);

    $this->actingAs($this->owner);

    Livewire\Livewire::test(App\Livewire\Holidays\Index::class)
        ->set('date', '2026-12-25')
        ->set('name', 'Natal Kedua')
        ->call('save')
        ->assertHasErrors('date');
});

it('menolak menetapkan peran pemilik lewat formulir anggota', function () {
    $this->actingAs($this->owner);

    Livewire\Livewire::test(Edit::class, ['membership' => Membership::where('user_id', $this->member->id)->firstOrFail()])
        ->set('role', 'owner')
        ->call('save')
        ->assertHasErrors('role');

    expect(Membership::where('user_id', $this->member->id)->firstOrFail()->role->value)->toBe('member');
});

it('menolak lokasi utama dari organisasi lain pada ubah anggota', function () {
    $foreignTenant = makeTenant(['slug' => 'organisasi-lain']);
    $foreignLocation = makeLocation($foreignTenant);

    $this->actingAs($this->owner);

    Livewire\Livewire::test(Edit::class, ['membership' => Membership::where('user_id', $this->member->id)->firstOrFail()])
        ->set('primary_location_id', (string) $foreignLocation->id)
        ->call('save')
        ->assertHasErrors('primary_location_id');

    expect(Membership::where('user_id', $this->member->id)->firstOrFail()->primary_location_id)->toBeNull();
});

it('menolak divisi dari organisasi lain pada ubah anggota', function () {
    $foreignTenant = makeTenant(['slug' => 'organisasi-divisi-lain']);
    $foreignDivision = makeDivision($foreignTenant);

    $this->actingAs($this->owner);

    Livewire\Livewire::test(Edit::class, ['membership' => Membership::where('user_id', $this->member->id)->firstOrFail()])
        ->set('division_id', (string) $foreignDivision->id)
        ->call('save')
        ->assertHasErrors('division_id');

    expect(Membership::where('user_id', $this->member->id)->firstOrFail()->division_id)->toBeNull();
});

it('menolak lokasi milik organisasi lain pada policy', function () {
    $foreignLocation = makeLocation(makeTenant(['slug' => 'organisasi-policy-lain']));

    $this->actingAs($this->owner);

    expect(Gate::allows('update', $foreignLocation))->toBeFalse()
        ->and(Gate::allows('view', $foreignLocation))->toBeFalse();
});

it('menolak admin mengubah pemilik', function () {
    $admin = makeUser();
    makeMembership($this->tenant, $admin, 'admin');
    $ownerMembership = Membership::where('user_id', $this->owner->id)->firstOrFail();

    $this->actingAs($admin);

    Livewire\Livewire::test(Edit::class, ['membership' => $ownerMembership])
        ->assertForbidden();
});
