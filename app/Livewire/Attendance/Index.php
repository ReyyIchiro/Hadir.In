<?php

namespace App\Livewire\Attendance;

use App\Enums\AttendanceStatus;
use App\Enums\MembershipRole;
use App\Livewire\Concerns\WithTenant;
use App\Models\AttendanceDay;
use App\Models\Division;
use App\Models\Location;
use App\Models\Membership;
use App\Models\Schedule;
use App\Models\TenantSetting;
use App\Services\TenantClock;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use WithTenant;

    public string $month = '';

    public string $date = '';

    public string $locationFilter = '';

    public string $divisionFilter = '';

    public string $statusFilter = '';

    public string $view = 'auto';

    public function mount(): void
    {
        $tenant = $this->tenant();
        $today = app(TenantClock::class)->today($tenant);

        $this->month = $today->format('Y-m');
        $this->date = $today->toDateString();
        $this->view = $this->membership()->role->canReview() ? 'team' : 'mine';
    }

    public function updatedMonth(): void
    {
        $this->resetPage();
    }

    public function updatedDate(): void
    {
        $this->resetPage();
    }

    public function updatedLocationFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDivisionFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function previousMonth(): void
    {
        $this->month = CarbonImmutable::createFromFormat('Y-m', $this->month)->subMonth()->format('Y-m');
        $this->resetPage();
    }

    public function nextMonth(): void
    {
        $next = CarbonImmutable::createFromFormat('Y-m', $this->month)->addMonth();

        if ($next->format('Y-m') <= now($this->tenant()->timezone)->format('Y-m')) {
            $this->month = $next->format('Y-m');
            $this->resetPage();
        }
    }

    public function shiftDate(int $days): void
    {
        $this->date = CarbonImmutable::parse($this->date)->addDays($days)->toDateString();
        $this->resetPage();
    }

    public function render(): View
    {
        $tenant = $this->tenant();
        $membership = $this->membership();
        $canReview = $membership->role->canReview();

        $locations = Location::orderBy('name')->get(['id', 'name']);
        $divisions = Division::orderBy('name')->get(['id', 'name']);

        if ($canReview && $this->view === 'team') {
            $data = $this->teamData($membership, $locations, $divisions);
        } else {
            $data = $this->personalData();
        }

        return view('livewire.attendance.index', array_merge($data, [
            'canReview' => $canReview,
            'locations' => $locations,
            'divisions' => $divisions,
            'statuses' => collect(AttendanceStatus::cases())
                ->mapWithKeys(fn (AttendanceStatus $status) => [$status->value => $status->label()])
                ->merge(['belum_absen' => 'Belum absen', 'belum_pulang' => 'Belum pulang', 'libur' => 'Libur'])
                ->all(),
        ]))->layoutData(['heading' => 'Presensi'])->title('Presensi');
    }

    /**
     * @return array<string, mixed>
     */
    protected function personalData(): array
    {
        $tenant = $this->tenant();
        $user = $this->currentUser();
        $start = CarbonImmutable::createFromFormat('Y-m', $this->month)->startOfMonth();
        $end = $start->endOfMonth();

        $days = AttendanceDay::where('user_id', $user->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('work_date')
            ->get();

        $schedules = Schedule::where('user_id', $user->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->where('is_day_off', false)
            ->get()
            ->keyBy(fn (Schedule $schedule) => $schedule->work_date->toDateString());

        $rows = $days->map(fn (AttendanceDay $day) => [
            'day' => $day,
            'date' => $day->work_date->toDateString(),
            'schedule' => $schedules->get($day->work_date->toDateString()),
        ]);

        $summary = [
            'hadir' => $days->filter(fn (AttendanceDay $d) => $d->status === AttendanceStatus::Hadir)->count(),
            'terlambat' => $days->filter(fn (AttendanceDay $d) => $d->status === AttendanceStatus::Terlambat)->count(),
            'izin' => $days->filter(fn (AttendanceDay $d) => in_array($d->status, [AttendanceStatus::Izin, AttendanceStatus::Sakit, AttendanceStatus::Cuti], true))->count(),
            'absen' => $days->filter(fn (AttendanceDay $d) => $d->status === AttendanceStatus::Absen)->count(),
        ];

        return [
            'rows' => $rows,
            'summary' => $summary,
            'days' => null,
            'total' => null,
            'totalPages' => null,
            'currentPage' => null,
        ];
    }

    /**
     * @param  Collection<int, Location>  $locations
     * @param  Collection<int, Division>  $divisions
     * @return array<string, mixed>
     */
    protected function teamData(Membership $membership, $locations, $divisions): array
    {
        $tenant = $this->tenant();
        $date = $this->date ?: app(TenantClock::class)->today($tenant)->toDateString();

        $query = Membership::where('is_active', true)->with(['user', 'division', 'primaryLocation']);

        if ($membership->role === MembershipRole::Reviewer && $membership->primary_location_id) {
            $query->where('primary_location_id', $membership->primary_location_id);
        }

        if ($this->locationFilter !== '') {
            $query->where('primary_location_id', (int) $this->locationFilter);
        }

        if ($this->divisionFilter !== '') {
            $query->where('division_id', (int) $this->divisionFilter);
        }

        $memberships = $query->get()->filter(fn (Membership $m) => $m->user !== null);

        $days = AttendanceDay::whereIn('user_id', $memberships->pluck('user_id'))
            ->where('work_date', $date)
            ->get()
            ->keyBy('user_id');

        $schedules = Schedule::whereIn('user_id', $memberships->pluck('user_id'))
            ->where('work_date', $date)
            ->get()
            ->keyBy('user_id');

        $clock = app(TenantClock::class);
        $settings = $tenant->settings ?? new TenantSetting(['tenant_id' => $tenant->id]);

        $rows = $memberships
            ->map(function (Membership $m) use ($days, $schedules, $clock, $settings, $tenant, $date) {
                $day = $days->get($m->user_id);
                $schedule = $schedules->get($m->user_id);

                if ($day !== null) {
                    $status = $day->displayStatus();
                } elseif ($schedule !== null && ! $schedule->is_day_off) {
                    $endTime = $schedule->end_time ?: $settings->work_end;
                    $due = $clock->isFinalizable($tenant, $date, $endTime, $settings->checkout_late_minutes);
                    $status = $due ? 'absen' : 'belum_absen';
                } else {
                    $status = 'libur';
                }

                return [
                    'membership' => $m,
                    'day' => $day,
                    'schedule' => $schedule,
                    'status' => $status,
                ];
            })
            ->when($this->statusFilter !== '', fn ($rows) => $rows->filter(fn ($row) => $row['status'] === $this->statusFilter))
            ->sortBy(fn ($row) => $row['membership']->user->name)
            ->values();

        $total = $rows->count();

        // Tampilan tim dibatasi 25 baris per halaman agar DOM Livewire tetap
        // kecil pada organisasi besar (ARCHITECTURE 14).
        $perPage = 25;
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $this->getPage()), $totalPages);
        $rows = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        return [
            'rows' => $rows,
            'summary' => null,
            'days' => null,
            'total' => $total,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ];
    }
}
