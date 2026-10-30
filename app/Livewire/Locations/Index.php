<?php

namespace App\Livewire\Locations;

use App\Livewire\Concerns\WithTenant;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Index extends Component
{
    use WithTenant;

    public ?string $flash = null;

    public function mount(): void
    {
        Gate::authorize('manage-settings', $this->membership());
    }

    public function toggle(int $locationId): void
    {
        Gate::authorize('manage-settings', $this->membership());

        $location = Location::findOrFail($locationId);
        $location->update(['is_active' => ! $location->is_active]);

        $this->flash = $location->is_active
            ? 'Lokasi diaktifkan kembali.'
            : 'Lokasi dinonaktifkan. Anggota tidak dapat absen di lokasi ini.';
    }

    public function render(): View
    {
        return view('livewire.locations.index', [
            'locations' => Location::withCount('memberships')->orderBy('name')->get(),
        ])->layoutData(['heading' => 'Lokasi'])->title('Lokasi');
    }
}
