<?php

use App\Livewire\Schedules\Edit;
use App\Livewire\Schedules\Index;
use App\Models\Schedule;
use Livewire\Livewire;

beforeEach(function () {
    $this->tenant = makeTenant();
    $this->owner = makeUser();
    makeMembership($this->tenant, $this->owner, 'owner');
    setTenant($this->tenant);
});

it('membuka halaman jadwal', function () {
    $this->actingAs($this->owner)->get('/app/jadwal')->assertOk();
});

it('menampilkan minggu berjalan beserta tautan atur', function () {
    Livewire::actingAs($this->owner)
        ->test(Index::class)
        ->assertOk()
        ->assertSee('Atur');
});

it('membuka halaman ubah untuk jadwal yang ada', function () {
    $date = now('Asia/Jakarta')->startOfWeek()->toDateString();
    $schedule = Schedule::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->owner->id,
        'work_date' => $date,
        'is_day_off' => false,
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
    ]);

    $this->actingAs($this->owner)
        ->get('/app/jadwal/'.$schedule->id.'/ubah')
        ->assertOk();
});

it('menyimpan perubahan jadwal lewat komponen', function () {
    $date = now('Asia/Jakarta')->startOfWeek()->toDateString();
    $schedule = Schedule::create([
        'tenant_id' => $this->tenant->id,
        'user_id' => $this->owner->id,
        'work_date' => $date,
        'is_day_off' => false,
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
    ]);

    Livewire::actingAs($this->owner)
        ->test(Edit::class, ['schedule' => $schedule])
        ->set('startTime', '09:00')
        ->set('endTime', '18:00')
        ->call('save')
        ->assertSet('flash', 'Jadwal disimpan.');

    expect($schedule->fresh()->start_time)->toContain('09:00');
});
