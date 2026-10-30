<?php

namespace App\Livewire\Members;

use App\Enums\MembershipRole;
use App\Livewire\Concerns\WithTenant;
use App\Models\Division;
use App\Models\Invitation;
use App\Models\Location;
use App\Models\Membership;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use WithTenant;

    public string $search = '';

    public string $roleFilter = '';

    public string $locationFilter = '';

    public string $divisionFilter = '';

    public function mount(): void
    {
        Gate::authorize('manage-members', $this->membership());
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'roleFilter', 'locationFilter', 'divisionFilter'], true)) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $membership = $this->membership();

        $query = Membership::with(['user', 'primaryLocation', 'division'])
            ->where('is_active', true);

        if ($membership->role === MembershipRole::Admin) {
            $query->whereIn('role', [MembershipRole::Member, MembershipRole::Reviewer]);
        }

        if ($this->search !== '') {
            $term = '%'.trim($this->search).'%';
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term));
        }

        if ($this->roleFilter !== '') {
            $query->where('role', $this->roleFilter);
        }

        if ($this->locationFilter !== '') {
            $query->where('primary_location_id', (int) $this->locationFilter);
        }

        if ($this->divisionFilter !== '') {
            $query->where('division_id', (int) $this->divisionFilter);
        }

        $pendingInvitations = Invitation::whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->count();

        return view('livewire.members.index', [
            'members' => $query->join('users', 'users.id', '=', 'memberships.user_id')
                ->orderBy('users.name')
                ->select('memberships.*')
                ->paginate(20),
            'locations' => Location::orderBy('name')->get(['id', 'name']),
            'divisions' => Division::orderBy('name')->get(['id', 'name']),
            'roles' => collect(MembershipRole::cases())
                ->reject(fn (MembershipRole $role) => $role === MembershipRole::Owner)
                ->mapWithKeys(fn (MembershipRole $role) => [$role->value => $role->label()]),
            'pendingInvitations' => $pendingInvitations,
        ])->layoutData(['heading' => 'Anggota'])->title('Anggota');
    }
}
