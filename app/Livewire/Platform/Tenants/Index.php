<?php

namespace App\Livewire\Platform\Tenants;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'statusFilter'], true)) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $query = Tenant::withCount(['memberships' => fn ($q) => $q->where('is_active', true)]);

        if ($this->search !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('slug', 'like', $term));
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.platform.tenants.index', [
            'tenants' => $query->orderBy('name')->paginate(20),
            'statuses' => collect(TenantStatus::cases())->mapWithKeys(fn (TenantStatus $status) => [$status->value => $status->label()]),
        ])->layout('components.layouts.platform')->title('Organisasi');
    }
}
