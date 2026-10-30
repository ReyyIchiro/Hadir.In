<?php

namespace App\Livewire\Members;

use App\Enums\MembershipRole;
use App\Livewire\Concerns\WithTenant;
use App\Models\Division;
use App\Models\Location;
use App\Models\Membership;
use App\Support\Waktu;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Edit extends Component
{
    use WithTenant;

    public Membership $member;

    public string $role = 'member';

    public string $primary_location_id = '';

    public string $division_id = '';

    public bool $is_active = true;

    public ?string $flash = null;

    public function mount(Membership $membership): void
    {
        $this->ensureInTenant($membership->tenant_id);

        Gate::authorize('update', $membership);

        $this->member = $membership->load('user');
        $this->role = $membership->role->value;
        $this->primary_location_id = (string) ($membership->primary_location_id ?? '');
        $this->division_id = (string) ($membership->division_id ?? '');
        $this->is_active = $membership->is_active;
    }

    public function save(): void
    {
        Gate::authorize('update', $this->member);

        $this->validate([
            'role' => ['required', 'in:admin,reviewer,member'],
            'primary_location_id' => ['nullable', 'integer'],
            'division_id' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ], [
            'role.required' => 'Peran wajib dipilih.',
        ]);

        if ($this->role === MembershipRole::Reviewer->value && $this->primary_location_id === '') {
            $this->addError('primary_location_id', 'Peninjau wajib memiliki lokasi utama.');

            return;
        }

        $actor = $this->membership();

        if ($actor->role === MembershipRole::Admin
            && in_array($this->role, [MembershipRole::Admin->value], true)) {
            $this->addError('role', 'Admin tidak dapat menetapkan peran admin.');

            return;
        }

        if ($this->member->user_id === $this->currentUser()->id && $this->is_active === false) {
            $this->addError('is_active', 'Anda tidak dapat menonaktifkan keanggotaan sendiri.');

            return;
        }

        $this->member->update([
            'role' => $this->role,
            'primary_location_id' => $this->primary_location_id !== '' ? (int) $this->primary_location_id : null,
            'division_id' => $this->division_id !== '' ? (int) $this->division_id : null,
            'is_active' => $this->is_active,
        ]);

        $this->flash = 'Data anggota diperbarui.';

        if (! $this->is_active) {
            $this->redirect(route('app.members.index'), navigate: true);
        }
    }

    public function render(): View
    {
        $actor = $this->membership();

        $roles = collect(MembershipRole::cases())
            ->reject(fn (MembershipRole $role) => $role === MembershipRole::Owner)
            ->reject(fn (MembershipRole $role) => $actor->role === MembershipRole::Admin && $role === MembershipRole::Admin)
            ->mapWithKeys(fn (MembershipRole $role) => [$role->value => $role->label()]);

        return view('livewire.members.edit', [
            'roles' => $roles,
            'locations' => Location::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'divisions' => Division::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ])->layoutData(['heading' => 'Ubah anggota'])->title('Ubah anggota');
    }
}
