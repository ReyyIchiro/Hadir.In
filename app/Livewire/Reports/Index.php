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

    public int $page = 1;

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
        $this->page = 1;

        if (! $this->isValidMonth($value)) {
            $this->month = Waktu::now($this->tenant())->format('Y-m');
        }
    }

    public function updatedDivisionId(): void
    {
        $this->page = 1;
    }

    public function updatedLocationId(): void
    {
        $this->page = 1;
    }

    public function previousPage(): void
    {
        $this->page = max(1, $this->page - 1);
    }

    public function nextPage(): void
    {
        $this->page++;
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
            $this->page,
        );

        $start = CarbonImmutable::createFromFormat('Y-m', $this->month, $tenant->timezone)->startOfMonth();
        $total = $data['total'];
        $totalPages = max(1, (int) ceil($total / 25));

        // Jeda aman: halaman bisa tersisa besar setelah filter menyempit.
        if ($this->page > $totalPages) {
            $this->page = $totalPages;
        }

        return view('livewire.reports.index', [
            'rows' => $data['rows'],
            'total' => $total,
            'totalPages' => $totalPages,
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
            'pdfExportUrl' => route('app.reports.export-pdf', array_filter([
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
