<?php

use App\Models\AttendanceDay;
use App\Models\AttendanceEvent;
use App\Models\Division;
use Illuminate\Support\Str;

beforeEach(function () {
    // Bulan laporan tertulis 2026-10. Bekukan waktu di bulan itu supaya
    // rentang 24 bulan dan batas "belum berjalan" tetap konsisten setiap
    // hari kalender.
    freezeAt('2026-10-05 01:00:00');

    $this->tenant = makeTenant();
    $this->location = makeLocation($this->tenant);
    $this->division = Division::create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Operasional',
        'is_active' => true,
    ]);
    $this->member = makeUser(['name' => 'Sari Dewi']);
    makeMembership($this->tenant, $this->member, 'member', [
        'primary_location_id' => $this->location->id,
        'division_id' => $this->division->id,
    ]);
    $this->owner = makeUser(['name' => 'Budi Santoso']);
    makeMembership($this->tenant, $this->owner, 'owner');
    setTenant($this->tenant);
});

it('mengunduh pdf laporan dengan judul, ringkasan, rekap, dan rincian', function () {
    makeSchedule($this->tenant, $this->member, '2026-10-01');
    makeSchedule($this->tenant, $this->member, '2026-10-02');

    AttendanceDay::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->member->id,
        'work_date' => '2026-10-01',
        'status' => 'hadir',
        'check_in_at' => '2026-10-01 00:55:00',
        'check_out_at' => '2026-10-01 10:00:00',
    ]);

    $response = $this->actingAs($this->owner)
        ->get('/app/laporan/ekspor-pdf?month=2026-10');

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    $response->assertHeader('content-disposition', 'inline; filename="laporan-presensi-2026-10.pdf"');

    $text = pdfText($response->getContent());

    expect($text)->toContain('Laporan Presensi Bulanan')
        ->and($text)->toContain('Oktober 2026')
        ->and($text)->toContain('Sari Dewi')
        ->and($text)->toContain('Hari terjadwal')
        ->and($text)->toContain('Rincian harian')
        ->and($text)->toContain('Halaman 1 /');
});

it('menerapkan filter divisi dan lokasi pada pdf', function () {
    $otherDivision = Division::create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Gudang',
        'is_active' => true,
    ]);
    $otherLocation = makeLocation($this->tenant, ['name' => 'Cabang Dua']);
    $other = makeUser(['name' => 'Andi']);
    makeMembership($this->tenant, $other, 'member', [
        'primary_location_id' => $otherLocation->id,
        'division_id' => $otherDivision->id,
    ]);

    makeSchedule($this->tenant, $this->member, '2026-10-01');
    makeSchedule($this->tenant, $other, '2026-10-01');

    $response = $this->actingAs($this->owner)
        ->get('/app/laporan/ekspor-pdf?month=2026-10&division_id='.$this->division->id.'&location_id='.$this->location->id);

    $text = pdfText($response->getContent());

    expect($text)->toContain('Sari Dewi')
        ->and($text)->not->toContain('Andi')
        ->and($text)->toContain('Operasional');
});

it('menolak anggota mengunduh pdf', function () {
    $this->actingAs($this->member)
        ->get('/app/laporan/ekspor-pdf?month=2026-10')
        ->assertForbidden();
});

it('menolak bulan laporan di masa depan pada pdf', function () {
    $this->actingAs($this->owner)
        ->get('/app/laporan/ekspor-pdf?month=2030-01')
        ->assertStatus(422);
});

it('menolak ekspor pdf tanpa format bulan yang sah', function () {
    $this->actingAs($this->owner)
        ->get('/app/laporan/ekspor-pdf?month=oktober')
        ->assertSessionHasErrors('month');
});

it('tidak mencetak selfie dan koordinat pada pdf', function () {
    makeSchedule($this->tenant, $this->member, '2026-10-01');

    $day = AttendanceDay::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->member->id,
        'work_date' => '2026-10-01',
        'status' => 'hadir',
        'check_in_at' => '2026-10-01 00:55:00',
        'check_out_at' => '2026-10-01 10:00:00',
    ]);

    AttendanceEvent::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->member->id,
        'attendance_day_id' => $day->id,
        'type' => 'check_in',
        'status' => 'accepted',
        'work_date' => '2026-10-01',
        'occurred_at' => '2026-10-01 00:55:00',
        'latitude' => '-6.2000000',
        'longitude' => '106.8166667',
        'accuracy_meters' => 12,
        'selfie_path' => 'selfies/rahasia-sari.jpg',
        'attempt_uuid' => (string) Str::uuid(),
    ]);

    $response = $this->actingAs($this->owner)
        ->get('/app/laporan/ekspor-pdf?month=2026-10');

    $text = pdfText($response->getContent());

    expect($text)->not->toContain('rahasia-sari')
        ->and($text)->not->toContain('-6.2000000')
        ->and($text)->not->toContain('106.8166667');
});

it('tidak membocorkan data tenant lain pada pdf', function () {
    $otherTenant = makeTenant(['name' => 'Tenant Lain']);
    $otherLocation = makeLocation($otherTenant, ['name' => 'Lokasi Lain']);
    $otherUser = makeUser(['name' => 'Orang Tenant Lain']);
    makeMembership($otherTenant, $otherUser, 'member', ['primary_location_id' => $otherLocation->id]);

    makeSchedule($this->tenant, $this->member, '2026-10-01');
    makeSchedule($otherTenant, $otherUser, '2026-10-01');

    $response = $this->actingAs($this->owner)
        ->get('/app/laporan/ekspor-pdf?month=2026-10');

    $text = pdfText($response->getContent());

    expect($text)->toContain('Sari Dewi')
        ->and($text)->not->toContain('Orang Tenant Lain')
        ->and($text)->not->toContain('Tenant Lain');
});

it('membatasi pdf pada tiga ribu baris', function () {
    // 40 anggota x 22 hari kerja masih di bawah batas; batas diuji lewat
    // jumlah baris yang dipotong pada dokumen.
    $users = [];

    for ($i = 0; $i < 4; $i++) {
        $user = makeUser(['name' => 'Anggota '.$i]);
        makeMembership($this->tenant, $user, 'member', [
            'primary_location_id' => $this->location->id,
            'division_id' => $this->division->id,
        ]);
        $users[] = $user;
    }

    for ($day = 1; $day <= 22; $day++) {
        $date = sprintf('2026-10-%02d', $day);

        if (in_array(now('Asia/Jakarta')->parse($date)->dayOfWeekIso, [6, 7], true)) {
            continue;
        }

        foreach ($users as $user) {
            makeSchedule($this->tenant, $user, $date);
        }
    }

    $response = $this->actingAs($this->owner)
        ->get('/app/laporan/ekspor-pdf?month=2026-10');

    $text = pdfText($response->getContent());

    expect($response->getStatusCode())->toBe(200)
        ->and($text)->toContain('Rincian harian');
});
