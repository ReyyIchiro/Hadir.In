<?php

use App\Models\AttendanceDay;
use App\Models\Division;
use App\Models\Holiday;
use App\Models\Membership;
use App\Services\ReportService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Bulan laporan tertulis 2026-10. Bekukan waktu di bulan itu supaya
    // rentang 24 bulan dan batas "belum berjalan" tetap konsisten setiap
    // hari kalender.
    freezeAt('2026-10-05 01:00:00');

    Storage::fake('local');
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

it('meringkas kehadiran bulanan per anggota', function () {
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

    AttendanceDay::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->member->id,
        'work_date' => '2026-10-02',
        'status' => 'absen',
        'late_minutes' => 0,
    ]);

    $data = app(ReportService::class)->monthData($this->tenant, '2026-10');

    expect($data['summary']['present'])->toBe(1)
        ->and($data['summary']['absent'])->toBe(1)
        ->and($data['summary']['scheduled'])->toBe(2)
        ->and($data['rows'])->toHaveCount(2);
});

it('menyaring laporan berdasarkan divisi', function () {
    $otherDivision = Division::create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Gudang',
        'is_active' => true,
    ]);
    $other = makeUser(['name' => 'Andi']);
    makeMembership($this->tenant, $other, 'member', [
        'primary_location_id' => $this->location->id,
        'division_id' => $otherDivision->id,
    ]);

    makeSchedule($this->tenant, $this->member, '2026-10-01');
    makeSchedule($this->tenant, $other, '2026-10-01');

    $data = app(ReportService::class)->monthData($this->tenant, '2026-10', $this->division->id);

    expect($data['rows'])->toHaveCount(1)
        ->and($data['rows'][0]['name'])->toBe('Sari Dewi');
});

it('mengekspor csv dengan pemisah titik koma dan bom utf8', function () {
    makeSchedule($this->tenant, $this->member, '2026-10-01');

    AttendanceDay::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->member->id,
        'work_date' => '2026-10-01',
        'status' => 'hadir',
        'check_in_at' => '2026-10-01 00:55:00',
        'check_out_at' => '2026-10-01 10:00:00',
    ]);

    $response = $this->actingAs($this->owner)
        ->get('/app/laporan/ekspor?month=2026-10');

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();

    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue()
        ->and($content)->toContain('Tanggal;Hari;Nama')
        ->and($content)->toContain('Sari Dewi');

    expect($response->headers->get('content-disposition'))->toContain('laporan-presensi-2026-10.csv');
});

it('membuat halaman baris laporan tanpa mengubah ringkasan', function () {
    makeSchedule($this->tenant, $this->member, '2026-10-01');
    makeSchedule($this->tenant, $this->member, '2026-10-02');
    makeSchedule($this->tenant, $this->member, '2026-10-03');

    $page1 = app(ReportService::class)->monthData($this->tenant, '2026-10', page: 1, perPage: 2);
    $page2 = app(ReportService::class)->monthData($this->tenant, '2026-10', page: 2, perPage: 2);
    $all = app(ReportService::class)->monthData($this->tenant, '2026-10');

    expect($page1['rows'])->toHaveCount(2)
        ->and($page1['total'])->toBe(3)
        ->and($page2['rows'])->toHaveCount(1)
        ->and($all['rows'])->toHaveCount(3)
        // Ringkasan selalu menghitung seluruh bulan, bukan halaman aktif.
        ->and($page1['summary']['scheduled'])->toBe(3)
        ->and($all['summary']['scheduled'])->toBe(3);
});

it('menetralisir sel csv yang diawali karakter formula', function () {
    $formula = makeUser(['name' => '=HYPERLINK("https://contoh.test")', 'email' => 'formula@hadir.test']);
    makeMembership($this->tenant, $formula, 'member', ['primary_location_id' => $this->location->id]);

    makeSchedule($this->tenant, $this->member, '2026-10-01');
    makeSchedule($this->tenant, $formula, '2026-10-01');

    $response = $this->actingAs($this->owner)
        ->get('/app/laporan/ekspor?month=2026-10');

    $content = $response->streamedContent();

    // Awalan kutip tunggal membuat Excel membaca sel sebagai teks, bukan formula.
    expect($content)->toContain("'=HYPERLINK");
});

it('menolak bulan laporan di masa depan', function () {
    $this->actingAs($this->owner)
        ->get('/app/laporan/ekspor?month=2030-01')
        ->assertStatus(422);
});

it('menolak ekspor tanpa format bulan yang sah', function () {
    $this->actingAs($this->owner)
        ->get('/app/laporan/ekspor?month=oktober')
        ->assertSessionHasErrors('month');
});

it('menolak anggota membuka laporan', function () {
    $this->actingAs($this->member)
        ->get('/app/laporan')
        ->assertForbidden();
});

it('mengizinkan peninjau membuka laporan', function () {
    $reviewer = makeUser();
    makeMembership($this->tenant, $reviewer, 'reviewer', ['primary_location_id' => $this->location->id]);

    $this->actingAs($reviewer)
        ->get('/app/laporan')
        ->assertOk();
});

it('membatasi peninjau pada lokasi utamanya di laporan', function () {
    $otherLocation = makeLocation($this->tenant, ['name' => 'Cabang Dua']);
    $other = makeUser(['name' => 'Andi']);
    makeMembership($this->tenant, $other, 'member', ['primary_location_id' => $otherLocation->id]);

    $reviewer = makeUser();
    makeMembership($this->tenant, $reviewer, 'reviewer', ['primary_location_id' => $this->location->id]);

    makeSchedule($this->tenant, $this->member, '2026-10-01');
    makeSchedule($this->tenant, $other, '2026-10-01');

    $viewer = Membership::where('user_id', $reviewer->id)->firstOrFail();
    $data = app(ReportService::class)->monthData($this->tenant, '2026-10', null, null, $viewer);

    expect($data['rows'])->toHaveCount(1)
        ->and($data['rows'][0]['name'])->toBe('Sari Dewi');
});

it('mengecualikan hari libur dari hari kerja', function () {
    makeSchedule($this->tenant, $this->member, '2026-10-01');
    makeSchedule($this->tenant, $this->member, '2026-10-02');

    Holiday::create([
        'tenant_id' => $this->tenant->id,
        'date' => '2026-10-02',
        'name' => 'Libur Nasional',
    ]);

    $data = app(ReportService::class)->monthData($this->tenant, '2026-10');

    expect($data['summary']['scheduled'])->toBe(1);
});

it('menolak bulan di luar 24 bulan terakhir', function () {
    $this->actingAs($this->owner)
        ->get('/app/laporan/ekspor?month=2020-01')
        ->assertStatus(422);
});

it('tidak menandai hari berjalan sebagai absen sebelum finalisasi', function () {
    freezeAt('2026-10-30 01:00:00');
    $today = now('Asia/Jakarta')->toDateString();

    makeSchedule($this->tenant, $this->member, $today);

    $data = app(ReportService::class)->monthData($this->tenant, now('Asia/Jakarta')->format('Y-m'));

    $row = collect($data['rows'])->firstWhere('date', $today);

    expect($row)->not->toBeNull()
        ->and($row['status'])->toBe('belum_absen')
        ->and($row['status_label'])->toBe('Belum absen')
        ->and($data['summary']['absent'])->toBe(0)
        ->and($data['summary']['not_finalized'])->toBe(1);
});

it('tidak menampilkan hari yang belum berjalan di laporan', function () {
    $future = now('Asia/Jakarta')->addDays(5)->toDateString();

    makeSchedule($this->tenant, $this->member, $future);

    $data = app(ReportService::class)->monthData($this->tenant, now('Asia/Jakarta')->format('Y-m'));

    expect($data['rows'])->toBeEmpty()
        ->and($data['summary']['scheduled'])->toBe(0);
});
