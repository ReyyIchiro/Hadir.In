<?php

namespace App\Livewire\Schedules;

use App\Enums\MembershipRole;
use App\Livewire\Concerns\WithTenant;
use App\Models\Division;
use App\Models\Membership;
use App\Models\Schedule;
use App\Services\TenantClock;
use App\Support\Waktu;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Index extends Component
{
    use WithTenant;

    public string $weekStart = '';

    public string $divisionFilter = '';

    public string $memberFilter = '';

    public function mount(): void
    {
        $this->weekStart = app(TenantClock::class)->today($this->tenant())->startOfWeek()->toDateString();
    }

    public function previousWeek(): void
    {
        $this->weekStart = CarbonImmutable::parse($this->weekStart)->subWeek()->toDateString();
    }

    public function nextWeek(): void
    {
        $this->weekStart = CarbonImmutable::parse($this->weekStart)->addWeek()->toDateString();
    }

    public function thisWeek(): void
    {
        $this->weekStart = app(TenantClock::class)->today($this->tenant())->startOfWeek()->toDateString();
    }

    public function render(): View
    {
        $tenant = $this->tenant();
        $membership = $this->membership();
        $canManage = $membership->role->canManageSchedules();
        $canReview = $membership->role->canReview();

        $start = CarbonImmutable::parse($this->weekStart);
        $end = $start->addDays(6);
        $dates = collect(range(0, 6))->map(fn (int $offset) => $start->addDays($offset));

        $query = Membership::where('is_active', true)->with('user');

        if ($membership->role === MembershipRole::Reviewer && $membership->primary_location_id) {
            $query->where('primary_location_id', $membership->primary_location_id);
        }

        if ($this->divisionFilter !== '') {
            $query->where('division_id', (int) $this->divisionFilter);
        }

        if ($this->memberFilter !== '') {
            $query->where('user_id', (int) $this->memberFilter);
        }

        if (! $canReview) {
            $query->where('user_id', $this->currentUser()->id);
        }

        $memberships = $query->get()->filter(fn (Membership $m) => $m->user !== null)->sortBy(fn (Membership $m) => $m->user->name);

        $schedules = Schedule::whereIn('user_id', $memberships->pluck('user_id'))
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn (Schedule $schedule) => $schedule->user_id.'|'.$schedule->work_date->toDateString());

        return view('livewire.schedules.index', [
            'dates' => $dates,
            'memberships' => $memberships,
            'schedules' => $schedules,
            'canManage' => $canManage,
            'divisions' => Division::orderBy('name')->get(['id', 'name']),
            'allMembers' => $canManage ? Membership::where('is_active', true)->with('user')->get()->filter(fn ($m) => $m->user !== null)->sortBy(fn ($m) => $m->user->name) : collect(),
        ])->layoutData(['heading' => 'Jadwal'])->title('Jadwal');
    }
}
