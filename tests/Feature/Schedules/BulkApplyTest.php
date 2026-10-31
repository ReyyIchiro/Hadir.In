<?php

use App\Livewire\Schedules\BulkApply;
use App\Models\Schedule;
use Livewire\Livewire;

beforeEach(function () {
    $this->tenant = makeTenant();
    $this->owner = makeUser();
    makeMembership($this->tenant, $this->owner, 'owner');
    setTenant($this->tenant);
});

it('membuka halaman terapkan massal tanpa galat', function () {
    $this->actingAs($this->owner)
        ->get('/app/jadwal/terapkan')
        ->assertOk();
});

it('membuka halaman terapkan massal dengan parameter member dan date', function () {
    $this->actingAs($this->owner)
        ->get('/app/jadwal/terapkan?member='.$this->owner->id.'&date=2026-10-31')
        ->assertOk();
});

it('menampilkan ringkasan saat komponen dimuat', function () {
    Livewire::actingAs($this->owner)
        ->test(BulkApply::class)
        ->assertSet('startDate', fn ($v) => is_string($v) && $v !== '')
        ->assertNotSet('preview', null);
});

it('menerapkan jadwal massal dan membuat baris jadwal', function () {
    Livewire::actingAs($this->owner)
        ->test(BulkApply::class)
        ->set('userIds', [$this->owner->id])
        ->set('startDate', '2026-11-02')
        ->set('endDate', '2026-11-06')
        ->set('weekdays', [1, 2, 3, 4, 5])
        ->set('startTime', '08:00')
        ->set('endTime', '17:00')
        ->call('apply')
        ->assertSet('flash', fn ($v) => is_string($v) && str_contains($v, 'diterapkan'));

    expect(Schedule::where('user_id', $this->owner->id)->count())->toBe(5);
});

it('menolak terapkan massal tanpa anggota', function () {
    Livewire::actingAs($this->owner)
        ->test(BulkApply::class)
        ->set('userIds', [])
        ->call('apply')
        ->assertHasErrors('userIds');
});
