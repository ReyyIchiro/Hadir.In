<?php

use App\Models\AttendanceDay;
use App\Models\Division;
use App\Models\Holiday;
use App\Models\Membership;
use App\Services\ReportService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
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
