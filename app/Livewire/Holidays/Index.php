<?php

namespace App\Livewire\Holidays;

use App\Livewire\Concerns\WithTenant;
use App\Models\Holiday;
use App\Services\TenantClock;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Index extends Component
{
    use WithTenant;

    public string $date = '';

    public string $name = '';

    public ?string $flash = null;

    public function mount(): void
    {
        Gate::authorize('manage-settings', $this->membership());
        $this->date = app(TenantClock::class)->today($this->tenant())->toDateString();
    }

    public function save(): void
    {
        Gate::authorize('manage-settings', $this->membership());

        $data = $this->validate([
            'date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:120'],
        ], [
            'date.required' => 'Tanggal libur wajib diisi.',
            'name.required' => 'Nama hari libur wajib diisi.',
        ]);

        if (Holiday::where('date', $data['date'])->exists()) {
            $this->addError('date', 'Tanggal ini sudah tercatat sebagai hari libur.');

            return;
        }

        Holiday::create([
            'tenant_id' => $this->tenant()->id,
            'date' => $data['date'],
            'name' => trim($data['name']),
        ]);

        $this->name = '';
        $this->flash = 'Hari libur ditambahkan.';
    }

    public function delete(int $holidayId): void
    {
        Gate::authorize('manage-settings', $this->membership());

        Holiday::whereKey($holidayId)->delete();
        $this->flash = 'Hari libur dihapus.';
    }

    public function render(): View
    {
        return view('livewire.holidays.index', [
            'holidays' => Holiday::orderByDesc('date')->get(),
        ])->layoutData(['heading' => 'Hari libur'])->title('Hari libur');
    }
}
