<?php

use App\Models\AttendanceDay;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Services\FinalizationService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->tenant = makeTenant();
    $this->location = makeLocation($this->tenant);
    $this->user = makeUser();
    makeMembership($this->tenant, $this->user, 'member', ['primary_location_id' => $this->location->id]);
    setTenant($this->tenant);
});

it('menandai hari terbuka tanpa pulang sebagai final otomatis', function () {
    makeSchedule($this->tenant, $this->user, '2026-10-30');

    AttendanceDay::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'work_date' => '2026-10-30',
        'status' => 'hadir',
        'check_in_at' => '2026-10-30 00:55:00',
    ]);

    // 21.30 WIB, setelah jam selesai 17.00 plus 240 menit.
    freezeAt('2026-10-30 14:30:00');

    $counts = app(FinalizationService::class)->runForTenant($this->tenant);
    $day = AttendanceDay::firstOrFail();

    expect($counts['finalized'])->toBe(1)
        ->and($day->finalized_at)->not->toBeNull()
        ->and($day->flags)->toContain('final_otomatis')
        ->and($day->flags)->toContain('tanpa_pulang');
});

it('tidak menyentuh hari yang belum melewati batas', function () {
    makeSchedule($this->tenant, $this->user, '2026-10-30');

    AttendanceDay::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'work_date' => '2026-10-30',
        'status' => 'hadir',
        'check_in_at' => '2026-10-30 00:55:00',
    ]);

    freezeAt('2026-10-30 10:00:00');

    $counts = app(FinalizationService::class)->runForTenant($this->tenant);

    expect($counts['finalized'])->toBe(0)
        ->and(AttendanceDay::firstOrFail()->finalized_at)->toBeNull();
});

it('membuat baris absen untuk jadwal terlewat', function () {
    makeSchedule($this->tenant, $this->user, '2026-10-29');
    freezeAt('2026-10-30 02:00:00');

    $counts = app(FinalizationService::class)->runForTenant($this->tenant);
    $day = AttendanceDay::firstOrFail();

    expect($counts['absent'])->toBe(1)
        ->and($day->status->value)->toBe('absen')
        ->and($day->finalized_at)->not->toBeNull();
});

it('tidak membuat absen pada hari libur', function () {
    makeSchedule($this->tenant, $this->user, '2026-10-29');
    Holiday::create([
        'tenant_id' => $this->tenant->id,
        'date' => '2026-10-29',
        'name' => 'Libur',
    ]);

    freezeAt('2026-10-30 02:00:00');

    $counts = app(FinalizationService::class)->runForTenant($this->tenant);

    expect($counts['absent'])->toBe(0)
        ->and(AttendanceDay::count())->toBe(0);
});

it('tidak membuat absen bila izin disetujui', function () {
    makeSchedule($this->tenant, $this->user, '2026-10-29');

    LeaveRequest::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->user->id,
        'type' => 'izin',
        'start_date' => '2026-10-29',
        'end_date' => '2026-10-29',
        'reason' => 'Keperluan keluarga.',
        'status' => 'approved',
    ]);

    freezeAt('2026-10-30 02:00:00');

    $counts = app(FinalizationService::class)->runForTenant($this->tenant);

    expect($counts['absent'])->toBe(0);
});

it('menyediakan perintah artisan finalisasi', function () {
    makeSchedule($this->tenant, $this->user, '2026-10-29');
    freezeAt('2026-10-30 02:00:00');

    $this->artisan('attendance:finalize')->assertSuccessful();

    expect(AttendanceDay::count())->toBe(1);
});
