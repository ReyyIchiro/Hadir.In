<?php

namespace App\Livewire\Locations;

use App\Livewire\Concerns\WithTenant;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Form extends Component
{
    use WithTenant;

    public ?Location $location = null;

    public string $name = '';

    public string $address = '';

    public string $latitude = '';

    public string $longitude = '';

    public string $radius_meters = '100';

    public bool $is_active = true;

    public function mount(?Location $location = null): void
    {
        Gate::authorize('manage-settings', $this->membership());

        if ($location && $location->exists) {
            $this->ensureInTenant($location->tenant_id);

            $this->location = $location;
            $this->name = $location->name;
            $this->address = $location->address ?? '';
            $this->latitude = (string) $location->latitude;
            $this->longitude = (string) $location->longitude;
            $this->radius_meters = (string) $location->radius_meters;
            $this->is_active = $location->is_active;
        }
    }

    public function save(): void
    {
        Gate::authorize('manage-settings', $this->membership());

        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:20', 'max:5000'],
            'is_active' => ['boolean'],
        ], [
            'name.required' => 'Nama lokasi wajib diisi.',
            'latitude.required' => 'Titik lokasi belum diisi.',
            'longitude.required' => 'Titik lokasi belum diisi.',
            'radius_meters.min' => 'Radius minimal 20 meter.',
            'radius_meters.max' => 'Radius maksimal 5000 meter.',
        ]);

        $payload = [
            'name' => trim($data['name']),
            'address' => $data['address'] !== '' ? trim($data['address']) : null,
            'latitude' => (float) $data['latitude'],
            'longitude' => (float) $data['longitude'],
            'radius_meters' => (int) $data['radius_meters'],
            'is_active' => $this->is_active,
        ];

        if ($this->location) {
            $this->location->update($payload);
        } else {
            $this->location = Location::create($payload + ['tenant_id' => $this->tenant()->id]);
        }

        $this->redirect(route('app.locations.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.locations.form')
            ->layoutData(['heading' => $this->location ? 'Ubah lokasi' : 'Tambah lokasi'])
            ->title($this->location ? 'Ubah lokasi' : 'Tambah lokasi');
    }
}
