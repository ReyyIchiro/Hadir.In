<?php

use App\Models\AttendanceCorrection;
use App\Models\AttendanceDay;
use App\Models\AttendanceEvent;
use App\Models\AttendanceReview;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->tenant = makeTenant();
});

it('menolak pemilik menyetujui pengajuan izin miliknya sendiri', function () {
    $owner = makeUser();
    makeMembership($this->tenant, $owner, 'owner');

    $leave = LeaveRequest::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $owner->id,
        'type' => 'izin',
        'start_date' => now()->toDateString(),
        'end_date' => now()->toDateString(),
        'reason' => 'Urusan pemilik.',
        'status' => 'pending',
    ]);

    $this->actingAs($owner);
    setTenant($this->tenant);

    expect(Gate::allows('review', $leave))->toBeFalse();
});

it('mengizinkan pemilik menyetujui pengajuan izin anggota lain', function () {
    $owner = makeUser();
    makeMembership($this->tenant, $owner, 'owner');
    $member = makeUser();
    makeMembership($this->tenant, $member, 'member');

    $leave = LeaveRequest::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $member->id,
        'type' => 'izin',
        'start_date' => now()->toDateString(),
        'end_date' => now()->toDateString(),
        'reason' => 'Urusan anggota.',
        'status' => 'pending',
    ]);

    $this->actingAs($owner);
    setTenant($this->tenant);

    expect(Gate::allows('review', $leave))->toBeTrue();
});

it('menolak peninjau memutus tinjauan kejadian miliknya sendiri', function () {
    $location = makeLocation($this->tenant);
    $reviewer = makeUser();
    makeMembership($this->tenant, $reviewer, 'reviewer', ['primary_location_id' => $location->id]);

    $day = AttendanceDay::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $reviewer->id,
        'work_date' => '2026-11-02',
        'status' => 'hadir',
    ]);

    $event = AttendanceEvent::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $reviewer->id,
        'attendance_day_id' => $day->id,
        'type' => 'check_in',
        'status' => 'rejected',
        'work_date' => '2026-11-02',
        'occurred_at' => now(),
        'attempt_uuid' => '11111111-1111-1111-1111-111111111111',
    ]);

    $review = AttendanceReview::create([
        'tenant_id' => $this->tenant->id,
        'attendance_event_id' => $event->id,
        'user_id' => $reviewer->id,
        'status' => 'pending',
        'note' => 'Mohon diperiksa ulang.',
    ]);

    $this->actingAs($reviewer);
    setTenant($this->tenant);

    expect(Gate::allows('decide', $review))->toBeFalse();
});

it('menolak anggota memutus koreksi miliknya sendiri', function () {
    $member = makeUser();
    makeMembership($this->tenant, $member, 'member');

    $day = AttendanceDay::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $member->id,
        'work_date' => '2026-11-02',
        'status' => 'hadir',
    ]);

    $correction = AttendanceCorrection::create([
        'tenant_id' => $this->tenant->id,
        'attendance_day_id' => $day->id,
        'user_id' => $member->id,
        'requested_by_id' => $member->id,
        'status' => 'pending',
        'original_values' => ['status' => 'hadir'],
        'requested_values' => ['status' => 'terlambat'],
        'reason' => 'Lupa absen.',
    ]);

    $this->actingAs($member);
    setTenant($this->tenant);

    expect(Gate::allows('decide', $correction))->toBeFalse();
});

it('menolak peninjau meninjau hari presensi miliknya sendiri', function () {
    $location = makeLocation($this->tenant);
    $reviewer = makeUser();
    makeMembership($this->tenant, $reviewer, 'reviewer', ['primary_location_id' => $location->id]);

    $day = AttendanceDay::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $reviewer->id,
        'work_date' => '2026-11-02',
        'status' => 'hadir',
        'location_id' => $location->id,
    ]);

    $this->actingAs($reviewer);
    setTenant($this->tenant);

    expect(Gate::allows('review', $day))->toBeFalse();
});

it('membiarkan alur batal izin sendiri tetap berjalan', function () {
    $member = makeUser();
    makeMembership($this->tenant, $member, 'member');

    $leave = LeaveRequest::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $member->id,
        'type' => 'izin',
        'start_date' => now()->toDateString(),
        'end_date' => now()->toDateString(),
        'reason' => 'Urusan.',
        'status' => 'pending',
    ]);

    $this->actingAs($member);
    setTenant($this->tenant);

    // Anggota tetap boleh membatalkan pengajuan menunggunya sendiri.
    expect(Gate::allows('cancel', $leave))->toBeTrue();
});
