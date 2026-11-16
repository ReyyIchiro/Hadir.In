<?php

use App\Services\GeofenceService;
use Illuminate\Support\Collection;

// Perpindahan 1 derajat lintang di ekuator sekitar 111.194,93 meter pada
// jari-jari bumi yang dipakai layanan ini. Dipakai untuk membuat titik uji
// pada jarak yang bisa dihitung dari mana saja.
const DEGREE_AT_EQUATOR = 111194.93;

function pointAtMeters(float $meters): float
{
    return $meters / DEGREE_AT_EQUATOR;
}

beforeEach(function () {
    $this->service = new GeofenceService;
});

it('menghitung jarak nol di titik yang sama', function () {
    expect($this->service->distance(-6.2, 106.8166667, -6.2, 106.8166667))->toBe(0);
});

it('menghitung jarak satu derajat lintang', function () {
    expect($this->service->distance(0.0, 0.0, 1.0, 0.0))->toBe(111194);
});

it('menentukan titik di dalam radius, batas radius, dan di luar radius', function () {
    // Lokasi pusat di titik nol, radius 50 meter. Titik uji di sepanjang
    // lintang ekuator sehingga jaraknya bisa diturunkan langsung.
    $location = makeLocation(makeTenant(), [
        'latitude' => 0.0,
        'longitude' => 0.0,
        'radius_meters' => 50,
    ]);

    // 44 meter: di dalam radius.
    $inRadius = $this->service->evaluate(
        collect([$location]),
        pointAtMeters(44),
        0.0,
        50,
    );
    expect($inRadius['inside'])->toBeTrue()
        ->and($inRadius['within_buffer'])->toBeFalse();

    // Tepat 50 meter: masih di dalam karena batasnya kurang dari atau sama.
    $atRadius = $this->service->evaluate(
        collect([$location]),
        pointAtMeters(50),
        0.0,
        50,
    );
    expect($atRadius['inside'])->toBeTrue();

    // 60 meter: keluar dari radius.
    $beyondRadius = $this->service->evaluate(
        collect([$location]),
        pointAtMeters(60),
        0.0,
        50,
    );
    expect($beyondRadius['inside'])->toBeFalse();
});

it('menerima titik dalam buffer dengan tanda di luar radius', function () {
    $location = makeLocation(makeTenant(), [
        'latitude' => 0.0,
        'longitude' => 0.0,
        'radius_meters' => 50,
    ]);

    // 99 meter: masih dalam radius ditambah buffer 50.
    $withinBuffer = $this->service->evaluate(
        collect([$location]),
        pointAtMeters(99),
        0.0,
        50,
    );
    expect($withinBuffer['inside'])->toBeFalse()
        ->and($withinBuffer['within_buffer'])->toBeTrue()
        ->and($withinBuffer['location']->is($location))->toBeTrue();

    // 130 meter: melewati radius dan buffer.
    $beyondBuffer = $this->service->evaluate(
        collect([$location]),
        pointAtMeters(130),
        0.0,
        50,
    );
    expect($beyondBuffer['within_buffer'])->toBeFalse()
        ->and($beyondBuffer['location'])->toBeNull();
});

it('memilih lokasi terdekat ketika ada beberapa lokasi', function () {
    $tenant = makeTenant();
    $far = makeLocation($tenant, [
        'name' => 'Kantor Jauh',
        'latitude' => 0.0,
        'longitude' => 0.0,
        'radius_meters' => 50,
    ]);
    $near = makeLocation($tenant, [
        'name' => 'Kantor Dekat',
        'latitude' => pointAtMeters(30),
        'longitude' => 0.0,
        'radius_meters' => 50,
    ]);

    // Titik 40 meter dari pusat nol: lebih dekat ke kantor dekat.
    $result = $this->service->evaluate(
        collect([$far, $near]),
        pointAtMeters(40),
        0.0,
        50,
    );

    expect($result['location']->is($near))->toBeTrue()
        ->and($result['inside'])->toBeTrue();
});

it('menolak lokasi wajib tanpa koordinat', function () {
    $tenant = makeTenant(['settings' => ['require_location' => true]]);
    makeLocation($tenant);

    $result = $this->service->validate($tenant, null, null, null);

    expect($result['accepted'])->toBeFalse()
        ->and($result['reason'])->toBe('lokasi_tidak_tersedia');
});

it('menerima lokasi tidak wajib tanpa koordinat dengan tanda', function () {
    $tenant = makeTenant(['settings' => ['require_location' => false]]);
    makeLocation($tenant);

    $result = $this->service->validate($tenant, null, null, null);

    expect($result['accepted'])->toBeTrue()
        ->and($result['flags'])->toContain('tanpa_lokasi')
        ->and($result['reason'])->toBeNull();
});

it('menerima organisasi tanpa lokasi aktif dengan tanda tanpa lokasi', function () {
    $tenant = makeTenant();
    // Tanpa lokasi sama sekali. require_location tidak berpengaruh karena
    // tidak ada titik pembanding (BR-25).

    $result = $this->service->validate($tenant, -6.2, 106.8166667, 10);

    expect($result['accepted'])->toBeTrue()
        ->and($result['flags'])->toContain('tanpa_lokasi')
        ->and($result['location'])->toBeNull();
});

it('menandai akurasi di atas ambang dan melewati akurasi tepat di ambang', function () {
    $tenant = makeTenant(['settings' => ['low_accuracy_meters' => 100]]);
    $location = makeLocation($tenant);

    $clean = $this->service->validate($tenant, -6.2, 106.8166667, 100);
    expect($clean['flags'])->not->toContain('akurasi_rendah');

    $weak = $this->service->validate($tenant, -6.2, 106.8166667, 101);
    expect($weak['flags'])->toContain('akurasi_rendah')
        ->and($weak['accepted'])->toBeTrue();
});

it('menolak akurasi di atas 500 meter walau di dalam radius', function () {
    $tenant = makeTenant(['settings' => ['low_accuracy_meters' => 100]]);
    $location = makeLocation($tenant);

    $result = $this->service->validate($tenant, -6.2, 106.8166667, 600);

    expect($result['accepted'])->toBeFalse()
        ->and($result['reason'])->toBe('akurasi_tidak_dapat_dipercaya')
        ->and($result['flags'])->toContain('akurasi_rendah')
        ->and($result['location'])->toBeNull();

    $boundary = $this->service->validate($tenant, -6.2, 106.8166667, 500);
    expect($boundary['accepted'])->toBeTrue();
});

it('menolak presensi jauh dari semua lokasi', function () {
    $tenant = makeTenant();
    makeLocation($tenant, ['radius_meters' => 50]);

    // Titik 500 meter dari lokasi: di luar radius dan buffer.
    $result = $this->service->validate($tenant, pointAtMeters(500), 0.0, 10);

    expect($result['accepted'])->toBeFalse()
        ->and($result['reason'])->toBe('di_luar_radius')
        ->and($result['distance'])->toBeGreaterThan(490);
});

it('mengembalikan hasil kosong ketika daftar lokasi kosong', function () {
    $result = $this->service->evaluate(new Collection, 0.0, 0.0, 50);

    expect($result['location'])->toBeNull()
        ->and($result['distance'])->toBeNull()
        ->and($result['inside'])->toBeFalse()
        ->and($result['within_buffer'])->toBeFalse();
});
