<?php

namespace App\Livewire\Divisions;

use App\Livewire\Concerns\WithTenant;
use App\Models\Division;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Index extends Component
{
    use WithTenant;

    public string $name = '';

    public string $description = '';

    public ?int $editingId = null;

    public ?string $flash = null;

    public function mount(): void
    {
        Gate::authorize('manage-settings', $this->membership());
    }

    public function save(): void
    {
        Gate::authorize('manage-settings', $this->membership());

        $data = $this->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama divisi wajib diisi.',
        ]);

        if ($this->editingId) {
            Division::findOrFail($this->editingId)->update([
                'name' => trim($data['name']),
                'description' => $data['description'] !== '' ? trim($data['description']) : null,
            ]);

            $this->flash = 'Divisi diperbarui.';
        } else {
            Division::create([
                'tenant_id' => $this->tenant()->id,
                'name' => trim($data['name']),
                'description' => $data['description'] !== '' ? trim($data['description']) : null,
            ]);

            $this->flash = 'Divisi ditambahkan.';
        }

        $this->reset('name', 'description', 'editingId');
    }

    public function edit(int $divisionId): void
    {
        Gate::authorize('manage-settings', $this->membership());

        $division = Division::findOrFail($divisionId);

        $this->editingId = $division->id;
        $this->name = $division->name;
        $this->description = $division->description ?? '';
    }

    public function cancelEdit(): void
    {
        $this->reset('name', 'description', 'editingId');
    }

    public function toggle(int $divisionId): void
    {
        Gate::authorize('manage-settings', $this->membership());

        $division = Division::findOrFail($divisionId);
        $division->update(['is_active' => ! $division->is_active]);
    }

    public function render(): View
    {
        return view('livewire.divisions.index', [
            'divisions' => Division::withCount('memberships')->orderBy('name')->get(),
        ])->layoutData(['heading' => 'Divisi'])->title('Divisi');
    }
}
