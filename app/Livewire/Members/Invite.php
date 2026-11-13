<?php

namespace App\Livewire\Members;

use App\Enums\MembershipRole;
use App\Livewire\Concerns\WithTenant;
use App\Models\Division;
use App\Models\Location;
use App\Services\InvitationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Invite extends Component
{
    use WithTenant;

    public string $email = '';

    public string $role = 'member';

    public string $primary_location_id = '';

    public string $division_id = '';

    public ?string $inviteLink = null;

    public ?string $flash = null;

    public function mount(): void
    {
        Gate::authorize('manage-members', $this->membership());
    }

    public function send(InvitationService $invitations): void
    {
        Gate::authorize('manage-members', $this->membership());

        $tenantId = $this->tenant()->id;

        $this->validate([
            'email' => ['required', 'email', 'max:190'],
            'role' => ['required', 'in:admin,reviewer,member'],
            'primary_location_id' => ['nullable', Rule::exists('locations', 'id')->where('tenant_id', $tenantId)],
            'division_id' => ['nullable', Rule::exists('divisions', 'id')->where('tenant_id', $tenantId)],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak sah.',
            'primary_location_id.exists' => 'Lokasi utama tidak ditemukan di organisasi ini.',
            'division_id.exists' => 'Divisi tidak ditemukan di organisasi ini.',
        ]);

        $actor = $this->membership();

        if ($actor->role === MembershipRole::Admin && $this->role === MembershipRole::Admin->value) {
            $this->addError('role', 'Admin tidak dapat mengundang admin lain.');

            return;
        }

        try {
            [$invitation, $token] = $invitations->create($this->tenant(), [
                'email' => $this->email,
                'role' => $this->role,
                'primary_location_id' => $this->primary_location_id !== '' ? (int) $this->primary_location_id : null,
                'division_id' => $this->division_id !== '' ? (int) $this->division_id : null,
            ], $this->currentUser());
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

        $this->inviteLink = route('invitations.show', $token);
        $this->flash = 'Undangan dibuat. Kirim tautan ini kepada anggota. Tautan berlaku 7 hari.';
        $this->email = '';
    }

    public function render(): View
    {
        $actor = $this->membership();

        $roles = collect(MembershipRole::cases())
            ->reject(fn (MembershipRole $role) => $role === MembershipRole::Owner)
            ->reject(fn (MembershipRole $role) => $actor->role === MembershipRole::Admin && $role === MembershipRole::Admin)
            ->mapWithKeys(fn (MembershipRole $role) => [$role->value => $role->label()]);

        return view('livewire.members.invite', [
            'roles' => $roles,
            'locations' => Location::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'divisions' => Division::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ])->layoutData(['heading' => 'Undang anggota'])->title('Undang anggota');
    }
}
