<?php

use App\Enums\AttendanceStatus;
use App\Services\TenantClock;

beforeEach(function () {
    $this->clock = new TenantClock;
});

it('menghitung tanggal lokal tenant, bukan tanggal UTC', function () {
    $tenant = makeTenant(['timezone' => 'Asia/Jakarta']);

    // 16.59 UTC masih 30 Oktober di Jakarta.
    freezeAt('2026-10-30 16:59:00');
    expect($this->clock->today($tenant)->toDateString())->toBe('2026-10-30');

    // 17.00 UTC sudah 31 Oktober di Jakarta.
    freezeAt('2026-10-30 17:00:00');
    expect($this->clock->today($tenant)->toDateString())->toBe('2026-10-31');
});

it('menghitung pergantian hari pada zona Makassar dan Jayapura', function () {
    $makassar = makeTenant(['timezone' => 'Asia/Makassar', 'slug' => 'uji-makassar']);
    $jayapura = makeTenant(['timezone' => 'Asia/Jayapura', 'slug' => 'uji-jayapura']);

    freezeAt('2026-10-30 15:59:00');
    expect($this->clock->today($makassar)->toDateString())->toBe('2026-10-30')
        ->and($this->clock->today($jayapura)->toDateString())->toBe('2026-10-31');
});

it('menggabungkan tanggal dan jam lokal menjadi waktu UTC', function () {
    $tenant = makeTenant(['timezone' => 'Asia/Jakarta']);

    $utc = $this->clock->atLocal($tenant, '2026-10-30', '08:00');

    expect($utc->format('Y-m-d H:i:s'))->toBe('2026-10-30 01:00:00')
        ->and($utc->getTimezone()->getName())->toBe('UTC');
});

it('menggabungkan jam lokal pada zona Makassar dan Jayapura', function () {
    $makassar = makeTenant(['timezone' => 'Asia/Makassar', 'slug' => 'uji-makassar']);
    $jayapura = makeTenant(['timezone' => 'Asia/Jayapura', 'slug' => 'uji-jayapura']);

    expect($this->clock->atLocal($makassar, '2026-10-30', '08:00')->format('Y-m-d H:i:s'))
        ->toBe('2026-10-30 00:00:00');

    expect($this->clock->atLocal($jayapura, '2026-10-30', '08:00')->format('Y-m-d H:i:s'))
        ->toBe('2026-10-29 23:00:00');
});

it('menghitung keterlambatan sejak jam mulai, bukan akhir toleransi', function () {
    $tenant = makeTenant(['timezone' => 'Asia/Jakarta']);
    $scheduledStart = $this->clock->localAt($tenant, '2026-10-30', '08:00');

    // Tepat di toleransi 10 menit: bukan terlambat.
    expect($this->clock->lateMinutes($this->clock->atLocal($tenant, '2026-10-30', '08:10'), $scheduledStart, 10))->toBe(0);

    // Satu menit melewati toleransi: 11 menit, dihitung dari jam mulai.
    expect($this->clock->lateMinutes($this->clock->atLocal($tenant, '2026-10-30', '08:11'), $scheduledStart, 10))->toBe(11);

    expect($this->clock->lateMinutes($this->clock->atLocal($tenant, '2026-10-30', '09:30'), $scheduledStart, 10))->toBe(90);
});

it('menghitung pulang cepat dari jam selesai', function () {
    $tenant = makeTenant(['timezone' => 'Asia/Jakarta']);
    $scheduledEnd = $this->clock->localAt($tenant, '2026-10-30', '17:00');

    expect($this->clock->earlyLeaveMinutes($this->clock->atLocal($tenant, '2026-10-30', '16:45'), $scheduledEnd))->toBe(15);

    // Lima puluh satu menit kurang: 9 menit, di bawah ambang 10.
    expect($this->clock->earlyLeaveMinutes($this->clock->atLocal($tenant, '2026-10-30', '16:51'), $scheduledEnd))->toBe(9);

    // Pulang setelah jam selesai: bukan pulang cepat.
    expect($this->clock->earlyLeaveMinutes($this->clock->atLocal($tenant, '2026-10-30', '21:00'), $scheduledEnd))->toBe(0);
});

it('menentukan status dari menit keterlambatan', function () {
    expect($this->clock->statusFor(0))->toBe(AttendanceStatus::Hadir)
        ->and($this->clock->statusFor(11))->toBe(AttendanceStatus::Terlambat);
});

it('menetapkan batas finalisasi pada jam selesai ditambah jendela pulang', function () {
    $tenant = makeTenant(['timezone' => 'Asia/Jakarta']);

    // 17.00 ditambah 240 menit berakhir 21.00 waktu lokal Jakarta.
    expect($this->clock->finalizationDeadline($tenant, '2026-10-30', '17:00', 240)->format('Y-m-d H:i:s'))
        ->toBe('2026-10-30 21:00:00')
        ->and($this->clock->finalizationDeadline($tenant, '2026-10-30', '17:00', 240)->getTimezone()->getName())
        ->toBe('Asia/Jakarta');
});

it('menilai hari finalisasi tepat pada batas', function () {
    $tenant = makeTenant(['timezone' => 'Asia/Jakarta']);

    freezeAt('2026-10-30 13:59:00');
    expect($this->clock->isFinalizable($tenant, '2026-10-30', '17:00', 240))->toBeFalse();

    freezeAt('2026-10-30 14:00:00');
    expect($this->clock->isFinalizable($tenant, '2026-10-30', '17:00', 240))->toBeTrue();
});
