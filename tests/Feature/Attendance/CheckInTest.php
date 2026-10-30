<?php

use App\Models\AttendanceDay;
use App\Models\AttendanceEvent;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    Storage::fake('local');
    $this->tenant = makeTenant();
    $this->location = makeLocation($this->tenant);
    $this->user = makeUser();
    makeMembership($this->tenant, $this->user, 'member', ['primary_location_id' => $this->location->id]);
    setTenant($this->tenant);
    makeSchedule($this->tenant, $this->user, '2026-10-30');
    $this->service = app(AttendanceService::class);
});

it('menerima absen masuk di dalam radius', function () {
    freezeAt('2026-10-30 00:55:00');

    $result = $this->service->checkIn($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 12,
    ], selfieFile());

    expect($result->accepted)->toBeTrue()
        ->and($result->status->value)->toBe('hadir')
        ->and($result->event->status->value)->toBe('accepted')
        ->and($result->day)->not->toBeNull()
        ->and($result->day->late_minutes)->toBe(0);

    $day = AttendanceDay::firstOrFail();
    expect($day->check_in_at->utc()->format('H:i'))->toBe('00:55');
});

it('menandai terlambat setelah masa tenggang', function () {
    freezeAt('2026-10-30 01:20:00');

    $result = $this->service->checkIn($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeTrue()
        ->and($result->status->value)->toBe('terlambat')
        ->and($result->day->late_minutes)->toBe(20);
});

it('tidak menandai terlambat di dalam masa tenggang', function () {
    freezeAt('2026-10-30 01:08:00');

    $result = $this->service->checkIn($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->status->value)->toBe('hadir')
        ->and($result->day->late_minutes)->toBe(0);
});

it('menolak absen terlalu awal', function () {
    // 06.30 WIB, lebih dari 60 menit sebelum jam mulai 08.00.
    freezeAt('2026-10-29 23:30:00');

    $result = $this->service->checkIn($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeFalse()
        ->and($result->reason)->toBe('terlalu_awal')
        ->and(AttendanceDay::count())->toBe(0);
});

it('menolak absen setelah jadwal berakhir', function () {
    freezeAt('2026-10-30 11:00:00');

    $result = $this->service->checkIn($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeFalse()
        ->and($result->reason)->toBe('jadwal_berakhir');
});

it('menolak absen kedua pada hari yang sama', function () {
    freezeAt('2026-10-30 00:55:00');

    $this->service->checkIn($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    $result = $this->service->checkIn($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeFalse()
        ->and($result->reason)->toBe('sudah_masuk')
        ->and(AttendanceDay::count())->toBe(1);
});

it('menolak absen di luar radius dan menyimpan kejadian', function () {
    freezeAt('2026-10-30 00:55:00');

    $result = $this->service->checkIn($this->user, $this->tenant, [
        'latitude' => -6.3000000,
        'longitude' => 106.9000000,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeFalse()
        ->and($result->reason)->toBe('di_luar_radius')
        ->and(AttendanceDay::count())->toBe(0)
        ->and(AttendanceEvent::count())->toBe(1)
        ->and($result->canRequestReview)->toBeTrue();
});

it('menerima absen di dalam zona toleransi radius', function () {
    freezeAt('2026-10-30 00:55:00');

    // Sekitar 80 m dari pusat, radius 50 m, toleransi 50 m.
    $result = $this->service->checkIn($this->user, $this->tenant, [
        'latitude' => -6.2007200,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeTrue()
        ->and($result->day->flags)->toContain('di_luar_radius');
});

it('menandai akurasi rendah', function () {
    freezeAt('2026-10-30 00:55:00');

    $result = $this->service->checkIn($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 250,
    ], selfieFile());

    expect($result->accepted)->toBeTrue()
        ->and($result->flags)->toContain('akurasi_rendah');
});

it('menerima tanpa lokasi bila belum ada lokasi kantor', function () {
    $tenant = makeTenant();
    $user = makeUser();
    makeMembership($tenant, $user, 'member');
    setTenant($tenant);
    makeSchedule($tenant, $user, '2026-10-30');

    freezeAt('2026-10-30 00:55:00');

    $result = app(AttendanceService::class)->checkIn($user, $tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeTrue()
        ->and($result->flags)->toContain('tanpa_lokasi');
});

it('menolak absen tanpa selfie bila diwajibkan', function () {
    freezeAt('2026-10-30 00:55:00');

    $result = $this->service->checkIn($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ]);

    expect($result->accepted)->toBeFalse()
        ->and($result->reason)->toBe('selfie_wajib');
});

it('menyimpan selfie di disk privat', function () {
    freezeAt('2026-10-30 00:55:00');

    $result = $this->service->checkIn($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->event->selfie_path)->toStartWith('selfies/'.$this->tenant->id.'/');
    Storage::disk('local')->assertExists($result->event->selfie_path);
});

it('menolak absen saat izin disetujui', function () {
    freezeAt('2026-10-30 00:55:00');

    LeaveRequest::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'type' => 'izin',
        'start_date' => '2026-10-30',
        'end_date' => '2026-10-30',
        'reason' => 'Keperluan keluarga.',
        'status' => 'approved',
    ]);

    $result = $this->service->checkIn($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeFalse()
        ->and($result->reason)->toBe('izin_disetujui');
});

it('menerima absen pada hari libur dengan tanda hari libur', function () {
    $user = makeUser();
    makeMembership($this->tenant, $user, 'member');
    Holiday::create([
        'tenant_id' => $this->tenant->id,
        'date' => '2026-10-31',
        'name' => 'Libur Nasional',
    ]);

    freezeAt('2026-10-31 00:55:00');

    $result = $this->service->checkIn($user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeTrue()
        ->and($result->flags)->toContain('hari_libur');
});

it('mengembalikan hasil yang sama untuk attempt uuid yang sama', function () {
    freezeAt('2026-10-30 00:55:00');

    $uuid = (string) Str::uuid();

    $first = $this->service->checkIn($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
        'attempt_uuid' => $uuid,
    ], selfieFile());

    $second = $this->service->checkIn($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
        'attempt_uuid' => $uuid,
    ], selfieFile());

    expect($second->event->id)->toBe($first->event->id)
        ->and(AttendanceEvent::count())->toBe(1);
});
