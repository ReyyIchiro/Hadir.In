<?php

use App\Models\AttendanceDay;
use App\Models\AttendanceEvent;
use App\Services\AttendanceService;
use App\Services\GeofenceService;
use Illuminate\Support\Facades\Storage;

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

function clockIn($test): AttendanceDay
{
    freezeAt('2026-10-30 00:55:00');

    $result = $test->service->checkIn($test->user, $test->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    return $result->day;
}

it('menerima absen pulang dan menutup hari', function () {
    clockIn($this);
    freezeAt('2026-10-30 10:05:00');

    $result = $this->service->checkOut($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeTrue()
        ->and($result->day->check_out_at)->not->toBeNull()
        ->and($result->day->early_leave_minutes)->toBe(0);
});

it('menolak absen pulang tanpa absen masuk', function () {
    freezeAt('2026-10-30 10:00:00');

    $result = $this->service->checkOut($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeFalse()
        ->and($result->reason)->toBe('belum_absen_masuk');
});

it('menolak absen pulang kedua', function () {
    clockIn($this);

    freezeAt('2026-10-30 10:05:00');
    $this->service->checkOut($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    freezeAt('2026-10-30 10:10:00');
    $result = $this->service->checkOut($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeFalse()
        ->and($result->reason)->toBe('sudah_pulang');
});

it('menolak absen pulang setelah 24 jam', function () {
    clockIn($this);

    freezeAt('2026-10-31 01:30:00');

    $result = $this->service->checkOut($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeFalse()
        ->and($result->reason)->toBe('melewati_batas_pulang');
});

it('menandai pulang cepat', function () {
    clockIn($this);
    // 08.00 WIB, sembilan jam sebelum jam selesai.
    freezeAt('2026-10-30 01:00:00');

    $result = $this->service->checkOut($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeTrue()
        ->and($result->flags)->toContain('pulang_cepat')
        ->and($result->day->early_leave_minutes)->toBe(540);
});

it('tidak menandai pulang cepat bila selisih di bawah ambang', function () {
    clockIn($this);
    freezeAt('2026-10-30 09:55:00');

    $result = $this->service->checkOut($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->flags)->not->toContain('pulang_cepat')
        ->and($result->day->early_leave_minutes)->toBe(0);
});

it('menandai pulang terlambat setelah empat jam', function () {
    clockIn($this);
    // 21.10 WIB, lebih dari empat jam setelah jam selesai 17.00.
    freezeAt('2026-10-30 14:10:00');

    $result = $this->service->checkOut($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeTrue()
        ->and($result->flags)->toContain('pulang_terlambat');
});

it('menolak absen pulang di luar radius', function () {
    clockIn($this);
    freezeAt('2026-10-30 10:00:00');

    $result = $this->service->checkOut($this->user, $this->tenant, [
        'latitude' => -6.4000000,
        'longitude' => 107.0000000,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeFalse()
        ->and($result->reason)->toBe('di_luar_radius')
        ->and($result->day->check_out_at)->toBeNull();
});

it('menutup hari terbuka sebelumnya saat absen masuk baru', function () {
    $previous = clockIn($this);

    // Hari berikutnya, masuk lagi pada jam yang sama.
    makeSchedule($this->tenant, $this->user, '2026-10-31');
    freezeAt('2026-10-31 00:55:00');

    $this->service->checkIn($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    $previous->refresh();

    expect($previous->finalized_at)->not->toBeNull()
        ->and($previous->flags)->toContain('tanpa_pulang')
        ->and($previous->flags)->toContain('final_otomatis');
});

it('menyimpan dua kejadian absen untuk satu hari', function () {
    clockIn($this);
    freezeAt('2026-10-30 10:05:00');
    $this->service->checkOut($this->user, $this->tenant, [
        'latitude' => -6.2000000,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect(AttendanceEvent::count())->toBe(2)
        ->and(AttendanceEvent::where('type', 'check_in')->count())->toBe(1)
        ->and(AttendanceEvent::where('type', 'check_out')->count())->toBe(1);
});

it('menghitung jarak memakai rumus haversine', function () {
    $geo = app(GeofenceService::class);

    // Monas ke titik sekitar 111 meter ke utara.
    $distance = $geo->distance(-6.175392, 106.827153, -6.176392, 106.827153);

    expect($distance)->toBeGreaterThan(100)->toBeLessThan(120);
});
