<?php

namespace App\Livewire\Reports;

use App\Livewire\Concerns\WithTenant;
use App\Models\Division;
use App\Models\Location;
use App\Services\ReportService;
use App\Support\Waktu;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Index extends Component
{
    use WithTenant;

    public string $month = '';

    public string $divisionId = '';

    public string $locationId = '';

    public function mount(): void
    {
        Gate::authorize('view-reports', $this->membership());

        $requested = (string) request()->query('month', '');

        $this->month = $this->isValidMonth($requested)
            ? $requested
            : Waktu::now($this->tenant())->format('Y-m');
    }

    public function updatedMonth(string $value): void
    {
        if (! $this->isValidMonth($value)) {
            $this->month = Waktu::now($this->tenant())->format('Y-m');
        }
    }

    public function render(ReportService $reports): View
    {
        $tenant = $this->tenant();
        $membership = $this->membership();

        $data = $reports->monthData(
            $tenant,
            $this->month,
            $this->divisionId !== '' ? (int) $this->divisionId : null,
            $this->locationId !== '' ? (int) $this->locationId : null,
            $membership,
        );

        $start = CarbonImmutable::createFromFormat('Y-m', $this->month, $tenant->timezone);

        return view('livewire.reports.index', [
            'rows' => $data['rows'],
            'summary' => $data['summary'],
            'start' => $start,
            'monthLabel' => Waktu::monthLabel($start->month).' '.$start->year,
            'divisions' => Division::orderBy('name')->get(['id', 'name']),
            'locations' => Location::orderBy('name')->get(['id', 'name']),
            'monthOptions' => $this->monthOptions(),
            'exportUrl' => route('app.reports.export', array_filter([
                'month' => $this->month,
                'division_id' => $this->divisionId !== '' ? $this->divisionId : null,
                'location_id' => $this->locationId !== '' ? $this->locationId : null,
            ])),
        ])->layoutData(['heading' => 'Laporan'])->title('Laporan');
    }

    protected function isValidMonth(string $value): bool
    {
        return (bool) preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value);
    }

    /**
     * @return array<string, string>
     */
    protected function monthOptions(): array
    {
        $now = Waktu::now($this->tenant())->startOfMonth();
        $options = [];

        for ($i = 0; $i < 24; $i++) {
            $cursor = $now->subMonths($i);
            $options[$cursor->format('Y-m')] = Waktu::monthLabel($cursor->month).' '.$cursor->year;
        }

        return $options;
    }
}
