<?php

use App\Enums\CorrectionStatus;
use App\Enums\ReviewStatus;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceDay;
use App\Models\AttendanceEvent;
use App\Models\AttendanceReview;
use App\Services\AttendanceService;
use App\Services\GeofenceService;
use App\Services\SelfieService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Storage::fake('local');
    $this->tenant = makeTenant();
    $this->location = makeLocation($this->tenant);
    $this->member = makeUser();
    makeMembership($this->tenant, $this->member, 'member', ['primary_location_id' => $this->location->id]);
    $this->reviewer = makeUser();
    makeMembership($this->tenant, $this->reviewer, 'reviewer', ['primary_location_id' => $this->location->id]);
    setTenant($this->tenant);
});

function rejectedCheckIn($test): AttendanceEvent
{
    makeSchedule($test->tenant, $test->member, '2026-10-30');
    freezeAt('2026-10-30 00:55:00');

    $result = app(AttendanceService::class)->checkIn($test->member, $test->tenant, [
        'latitude' => -6.4000000,
        'longitude' => 107.0000000,
        'accuracy' => 10,
    ], selfieFile());

    return $result->event;
}

it('menyimpan pengajuan tinjauan untuk kejadian ditolak', function () {
    $event = rejectedCheckIn($this);

    $review = AttendanceReview::create([
        'tenant_id' => $this->tenant->id,
        'attendance_event_id' => $event->id,
        'user_id' => $this->member->id,
        'status' => ReviewStatus::Pending,
        'note' => 'GPS meleset.',
    ]);

    expect($review->status)->toBe(ReviewStatus::Pending)
        ->and($review->event->id)->toBe($event->id);
});

it('menerima kejadian yang ditinjau dan membuat hari presensi', function () {
    $event = rejectedCheckIn($this);

    $day = app(AttendanceService::class)->acceptReviewedEvent($event, $this->reviewer);

    expect($day->exists)->toBeTrue()
        ->and($day->status->value)->toBe('hadir')
        ->and($day->flags)->toContain('diterima_manual')
        ->and($event->refresh()->status->value)->toBe('accepted');
});

it('menolak menerima kejadian yang sudah diterima', function () {
    $event = rejectedCheckIn($this);

    app(AttendanceService::class)->acceptReviewedEvent($event, $this->reviewer);

    expect(fn () => app(AttendanceService::class)->acceptReviewedEvent($event->refresh(), $this->reviewer))
        ->toThrow(ValidationException::class);
});

it('menghitung jarak dengan benar untuk titik di luar radius', function () {
    $geo = app(GeofenceService::class);
    $distance = $geo->distance(-6.2, 106.8166667, -6.4, 107.0);

    expect($distance)->toBeGreaterThan(20000);
});

it('menolak peninjau mengubah data di luar lokasi utamanya', function () {
    $otherLocation = makeLocation($this->tenant, ['name' => 'Lokasi Lain']);
    $otherMember = makeUser();
    makeMembership($this->tenant, $otherMember, 'member', ['primary_location_id' => $otherLocation->id]);

    makeSchedule($this->tenant, $otherMember, '2026-10-30');
    freezeAt('2026-10-30 00:55:00');

    $result = app(AttendanceService::class)->checkIn($otherMember, $this->tenant, [
        'latitude' => -6.4000000,
        'longitude' => 107.0000000,
        'accuracy' => 10,
    ], selfieFile());

    $review = AttendanceReview::create([
        'tenant_id' => $this->tenant->id,
        'attendance_event_id' => $result->event->id,
        'user_id' => $otherMember->id,
        'status' => ReviewStatus::Pending,
        'note' => 'GPS meleset.',
    ]);

    $this->actingAs($this->reviewer)
        ->get('/app/tinjauan/presensi/'.$review->id)
        ->assertForbidden();
});

it('mengizinkan peninjau membuka kejadian di lokasi utamanya', function () {
    $event = rejectedCheckIn($this);

    $review = AttendanceReview::create([
        'tenant_id' => $this->tenant->id,
        'attendance_event_id' => $event->id,
        'user_id' => $this->member->id,
        'status' => ReviewStatus::Pending,
        'note' => 'GPS meleset.',
    ]);

    $this->actingAs($this->reviewer)
        ->get('/app/tinjauan/presensi/'.$review->id)
        ->assertOk();
});

it('menolak anggota membuka daftar tinjauan', function () {
    $this->actingAs($this->member)
        ->get('/app/tinjauan')
        ->assertForbidden();
});

it('menerapkan koreksi dan menghitung ulang menit terlambat', function () {
    makeSchedule($this->tenant, $this->member, '2026-10-30');

    $day = AttendanceDay::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->member->id,
        'work_date' => '2026-10-30',
        'status' => 'hadir',
        'check_in_at' => '2026-10-30 00:55:00',
        'check_out_at' => null,
    ]);

    $updated = app(AttendanceService::class)->applyCorrection($day, [
        'check_in_at' => '2026-10-30 01:30:00',
        'check_out_at' => '2026-10-30 10:00:00',
        'note' => 'Lupa absen pagi.',
    ]);

    expect($updated->is_corrected)->toBeTrue()
        ->and($updated->late_minutes)->toBe(30)
        ->and($updated->flags)->toContain('dikoreksi')
        ->and($updated->note)->toBe('Lupa absen pagi.');
});

it('menolak koreksi dengan waktu pulang sebelum masuk', function () {
    makeSchedule($this->tenant, $this->member, '2026-10-30');

    $day = AttendanceDay::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->member->id,
        'work_date' => '2026-10-30',
        'status' => 'hadir',
        'check_in_at' => '2026-10-30 00:55:00',
    ]);

    expect(fn () => app(AttendanceService::class)->applyCorrection($day, [
        'check_in_at' => '2026-10-30 02:00:00',
        'check_out_at' => '2026-10-30 01:00:00',
    ]))->toThrow(ValidationException::class);
});

it('menolak akses selfie bagi anggota lain', function () {
    $event = rejectedCheckIn($this);

    $other = makeUser();
    makeMembership($this->tenant, $other, 'member', ['primary_location_id' => $this->location->id]);

    $url = app(SelfieService::class)->temporaryUrl($event->id);

    $this->actingAs($other)->get($url)->assertForbidden();
});

it('menampilkan kejadian di lokasi peninjau walau anggota berlokasi utama lain', function () {
    // Anggota ini lokasi utamanya bukan lokasi peninjau, tetapi ia absen di
    // lokasi peninjau. BR-62 menyatakan kejadian seperti ini tetap masuk
    // lingkup peninjauan.
    $otherLocation = makeLocation($this->tenant, ['name' => 'Cabang']);
    $outsider = makeUser();
    makeMembership($this->tenant, $outsider, 'member', ['primary_location_id' => $otherLocation->id]);

    makeSchedule($this->tenant, $outsider, '2026-10-30');
    freezeAt('2026-10-30 00:55:00');

    $result = app(AttendanceService::class)->checkIn($outsider, $this->tenant, [
        'latitude' => (float) $this->location->latitude,
        'longitude' => (float) $this->location->longitude,
        'accuracy' => 10,
    ], selfieFile());

    // Paksa kejadian tercatat sebagai ditolak pada lokasi peninjau.
    $event = $result->event;
    $event->forceFill([
        'status' => 'rejected',
        'reason' => 'di_luar_radius',
        'location_id' => $this->location->id,
    ])->save();

    $review = AttendanceReview::create([
        'tenant_id' => $this->tenant->id,
        'attendance_event_id' => $event->id,
        'user_id' => $outsider->id,
        'status' => ReviewStatus::Pending,
        'note' => 'Minta ditinjau.',
    ]);

    $this->actingAs($this->reviewer)
        ->get('/app/tinjauan')
        ->assertOk()
        ->assertSee($outsider->name);

    expect($review->refresh()->status)->toBe(ReviewStatus::Pending);
});

it('mengizinkan peninjau memutuskan koreksi pada hari di lokasi peninjau', function () {
    $otherLocation = makeLocation($this->tenant, ['name' => 'Cabang']);
    $outsider = makeUser();
    makeMembership($this->tenant, $outsider, 'member', ['primary_location_id' => $otherLocation->id]);

    $day = AttendanceDay::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $outsider->id,
        'work_date' => '2026-10-29',
        'status' => 'hadir',
        'location_id' => $this->location->id,
        'check_in_at' => '2026-10-29 00:55:00',
    ]);

    $correction = AttendanceCorrection::create([
        'tenant_id' => $this->tenant->id,
        'attendance_day_id' => $day->id,
        'user_id' => $outsider->id,
        'requested_by_id' => $outsider->id,
        'status' => CorrectionStatus::Pending,
        'original_values' => ['check_in_at' => '2026-10-29 00:55:00'],
        'requested_values' => ['check_in_at' => '2026-10-29 01:00:00'],
        'reason' => 'Salah tekan.',
    ]);

    $this->actingAs($this->reviewer);
    setTenant($this->tenant);

    expect($this->reviewer->can('decide', $correction))->toBeTrue();
});

it('menolak peninjau memutuskan koreksi di luar lokasinya', function () {
    $otherLocation = makeLocation($this->tenant, ['name' => 'Cabang']);
    $outsider = makeUser();
    makeMembership($this->tenant, $outsider, 'member', ['primary_location_id' => $otherLocation->id]);

    $day = AttendanceDay::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $outsider->id,
        'work_date' => '2026-10-29',
        'status' => 'hadir',
        'location_id' => $otherLocation->id,
        'check_in_at' => '2026-10-29 00:55:00',
    ]);

    $correction = AttendanceCorrection::create([
        'tenant_id' => $this->tenant->id,
        'attendance_day_id' => $day->id,
        'user_id' => $outsider->id,
        'requested_by_id' => $outsider->id,
        'status' => CorrectionStatus::Pending,
        'original_values' => ['check_in_at' => '2026-10-29 00:55:00'],
        'requested_values' => ['check_in_at' => '2026-10-29 01:00:00'],
        'reason' => 'Salah tekan.',
    ]);

    $this->actingAs($this->reviewer);
    setTenant($this->tenant);

    expect($this->reviewer->can('decide', $correction))->toBeFalse();
});
