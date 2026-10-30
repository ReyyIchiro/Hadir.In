<?php

namespace App\Livewire\Members;

use App\Livewire\Concerns\WithTenant;
use App\Models\Invitation;
use App\Services\InvitationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Invitations extends Component
{
    use WithTenant;

    public ?string $flash = null;

    public function mount(): void
    {
        Gate::authorize('manage-members', $this->membership());
    }

    public function revoke(int $invitationId, InvitationService $invitations): void
    {
        Gate::authorize('manage-members', $this->membership());

        $invitation = Invitation::findOrFail($invitationId);

        Gate::authorize('delete', $invitation);

        $invitations->revoke($invitation);

        $this->flash = 'Undangan dicabut.';
    }

    public function render(): View
    {
        return view('livewire.members.invitations', [
            'invitations' => Invitation::with(['invitedBy', 'primaryLocation', 'division'])
                ->orderByDesc('created_at')
                ->get(),
        ])->layoutData(['heading' => 'Undangan'])->title('Undangan');
    }
}
