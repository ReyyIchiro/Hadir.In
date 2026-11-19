<?php

use App\Mail\SendLeaveStatusEmail;
use App\Models\AttendanceDay;
use App\Models\Holiday;
use App\Services\AttendanceService;
use App\Services\LeaveService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    // Tanggal pengajuan di tes ini tertulis langsung. Bekukan waktu pada
    // hari sebelum tanggal terawal agar validasi BR-44 tetap lolos setiap
    // hari kalender, bukan hanya pada hari tes ditulis.
    freezeAt('2026-11-02 00:55:00');

    Storage::fake('local');
    $this->tenant = makeTenant();
    $this->location = makeLocation($this->tenant);
    $this->member = makeUser();
    makeMembership($this->tenant, $this->member, 'member', ['primary_location_id' => $this->location->id]);
    $this->owner = makeUser();
    makeMembership($this->tenant, $this->owner, 'owner');
    setTenant($this->tenant);
});

it('mengajukan izin dengan status menunggu', function () {
    $leave = app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'izin',
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-03',
        'reason' => 'Ada urusan keluarga.',
    ]);

    expect($leave->status->value)->toBe('pending')
        ->and($leave->type->value)->toBe('izin');
});

it('menolak rentang lebih dari 30 hari', function () {
    expect(fn () => app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'izin',
        'start_date' => '2026-11-02',
        'end_date' => '2026-12-15',
        'reason' => 'Panjang.',
    ]))->toThrow(ValidationException::class);
});

it('menolak rentang bertumpang tindih', function () {
    app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'izin',
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-03',
        'reason' => 'Pertama.',
    ]);

    expect(fn () => app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'sakit',
        'start_date' => '2026-11-03',
        'end_date' => '2026-11-04',
        'reason' => 'Kedua.',
    ]))->toThrow(ValidationException::class);
});

it('menolak cuti tanpa hari kerja terjadwal', function () {
    expect(fn () => app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'cuti',
        'start_date' => '2026-11-07',
        'end_date' => '2026-11-08',
        'reason' => 'Libur akhir pekan.',
    ]))->toThrow(ValidationException::class);
});

it('menerima cuti pada hari kerja terjadwal', function () {
    makeSchedule($this->tenant, $this->member, '2026-11-02');

    $leave = app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'cuti',
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-02',
        'reason' => 'Cuti tahunan.',
    ]);

    expect($leave->exists)->toBeTrue();
});

it('menyetujui izin dan membuat hari presensi', function () {
    makeSchedule($this->tenant, $this->member, '2026-11-02');
    makeSchedule($this->tenant, $this->member, '2026-11-03');

    $leave = app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'izin',
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-03',
        'reason' => 'Urusan keluarga.',
    ]);

    app(LeaveService::class)->approve($leave, $this->owner, 'Disetujui.');

    expect($leave->refresh()->status->value)->toBe('approved')
        ->and(AttendanceDay::count())->toBe(2)
        ->and(AttendanceDay::first()->status->value)->toBe('izin');
});

it('menolak menyetujui pengajuan yang sudah diproses', function () {
    $leave = app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'izin',
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-02',
        'reason' => 'Urusan.',
    ]);

    app(LeaveService::class)->reject($leave, $this->owner, 'Tidak perlu.');

    expect(fn () => app(LeaveService::class)->approve($leave->refresh(), $this->owner))
        ->toThrow(ValidationException::class);
});

it('membatalkan izin dan menghapus hari presensi kosong', function () {
    makeSchedule($this->tenant, $this->member, '2026-11-02');

    $leave = app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'izin',
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-02',
        'reason' => 'Urusan.',
    ]);

    app(LeaveService::class)->approve($leave, $this->owner);
    // Izin yang sudah disetujui dibatalkan oleh peninjau atau admin.
    app(LeaveService::class)->cancel($leave->refresh(), $this->owner);

    expect($leave->refresh()->status->value)->toBe('cancelled')
        ->and(AttendanceDay::count())->toBe(0);
});

it('menolak anggota membatalkan izin orang lain', function () {
    $leave = app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'izin',
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-02',
        'reason' => 'Urusan.',
    ]);

    $other = makeUser();
    makeMembership($this->tenant, $other, 'member', ['primary_location_id' => $this->location->id]);

    expect(fn () => app(LeaveService::class)->cancel($leave, $other))->toThrow(HttpException::class);
});

it('mencegah absen masuk saat izin disetujui', function () {
    makeSchedule($this->tenant, $this->member, '2026-10-30');
    freezeAt('2026-10-30 00:55:00');

    $leave = app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'izin',
        'start_date' => '2026-10-30',
        'end_date' => '2026-10-30',
        'reason' => 'Urusan.',
    ]);
    app(LeaveService::class)->approve($leave, $this->owner);

    $result = app(AttendanceService::class)->checkIn($this->member, $this->tenant, [
        'latitude' => -6.2,
        'longitude' => 106.8166667,
        'accuracy' => 10,
    ], selfieFile());

    expect($result->accepted)->toBeFalse()
        ->and($result->reason)->toBe('izin_disetujui');
});

it('mengizinkan peninjau membatalkan izin yang disetujui', function () {
    makeSchedule($this->tenant, $this->member, '2026-11-02');

    $leave = app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'izin',
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-02',
        'reason' => 'Urusan.',
    ]);
    app(LeaveService::class)->approve($leave, $this->owner);

    app(LeaveService::class)->cancel($leave->refresh(), $this->owner);

    expect($leave->refresh()->status->value)->toBe('cancelled');
});

it('tidak membuat hari presensi izin pada tanggal tanpa jadwal', function () {
    // BR-46 dan FR-47: persetujuan izin hanya membuat hari kerja terjadwal
    // yang bukan hari libur, berlaku untuk semua jenis pengajuan.
    makeSchedule($this->tenant, $this->member, '2026-11-03');

    $leave = app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'izin',
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-03',
        'reason' => 'Urusan keluarga.',
    ]);

    app(LeaveService::class)->approve($leave, $this->owner);

    $days = AttendanceDay::orderBy('work_date')->get(['work_date', 'status']);

    expect($days)->toHaveCount(1)
        ->and($days->first()->work_date->toDateString())->toBe('2026-11-03')
        ->and($days->first()->status->value)->toBe('izin');
});

it('tidak membuat hari presensi izin pada hari libur', function () {
    makeSchedule($this->tenant, $this->member, '2026-11-02');
    Holiday::create([
        'tenant_id' => $this->tenant->id,
        'date' => '2026-11-02',
        'name' => 'Hari Uji',
    ]);

    $leave = app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'izin',
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-02',
        'reason' => 'Urusan keluarga.',
    ]);

    app(LeaveService::class)->approve($leave, $this->owner);

    expect(AttendanceDay::count())->toBe(0);
});

it('mengirim email status izin lewat antrean saat disetujui dan ditolak', function () {
    Mail::fake();

    makeSchedule($this->tenant, $this->member, '2026-11-02');

    $leave = app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'izin',
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-02',
        'reason' => 'Urusan keluarga.',
    ]);

    app(LeaveService::class)->approve($leave, $this->owner, 'Setuju.');

    Mail::assertQueued(SendLeaveStatusEmail::class, 1);

    Mail::assertQueued(SendLeaveStatusEmail::class, function (SendLeaveStatusEmail $mail) {
        return $mail->hasTo($this->member->email)
            && $mail->statusLabel === 'Disetujui'
            && $mail->typeName === 'Izin'
            && $mail->reviewNote === 'Setuju.';
    });

    // Pengajuan baru ditolak, email kedua dikirim dengan status Ditolak.
    $second = app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'sakit',
        'start_date' => '2026-11-03',
        'end_date' => '2026-11-03',
        'reason' => 'Sakit.',
    ]);

    app(LeaveService::class)->reject($second, $this->owner);

    Mail::assertQueued(SendLeaveStatusEmail::class, 2);
});

it('menampilkan halaman pengajuan izin untuk anggota', function () {
    $this->actingAs($this->member)
        ->get('/app/izin')
        ->assertOk();
});

it('menolak anggota membuka antrean tinjauan', function () {
    $this->actingAs($this->member)
        ->get('/app/tinjauan')
        ->assertForbidden();
});

it('mengizinkan peninjau membuka antrean tinjauan', function () {
    $this->actingAs($this->owner)
        ->get('/app/tinjauan')
        ->assertOk();
});

it('menolak anggota membuka detail tinjauan izin', function () {
    $leave = app(LeaveService::class)->submit($this->member, $this->tenant, [
        'type' => 'izin',
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-02',
        'reason' => 'Urusan.',
    ]);

    $this->actingAs($this->member)
        ->get('/app/tinjauan/izin/'.$leave->id)
        ->assertForbidden();
});
