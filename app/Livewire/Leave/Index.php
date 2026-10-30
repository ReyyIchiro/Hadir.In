<?php

namespace App\Livewire\Leave;

use App\Enums\LeaveStatus;
use App\Enums\MembershipRole;
use App\Livewire\Concerns\WithTenant;
use App\Models\LeaveRequest;
use App\Models\Membership;
use App\Support\Waktu;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use WithTenant;

    public string $statusFilter = '';

    public string $scope = 'mine';

    public function mount(): void
    {
        $this->scope = $this->membership()->role->canReview() ? 'team' : 'mine';
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedScope(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $tenant = $this->tenant();
        $membership = $this->membership();
        $canReview = $membership->role->canReview();
        $isTeam = $canReview && $this->scope === 'team';

        $query = LeaveRequest::with(['user', 'reviewedBy'])->orderByDesc('created_at');

        if ($isTeam) {
            if ($membership->role === MembershipRole::Reviewer && $membership->primary_location_id) {
                $userIds = Membership::where('primary_location_id', $membership->primary_location_id)
                    ->where('is_active', true)
                    ->pluck('user_id');

                $query->whereIn('user_id', $userIds);
            }
        } else {
            $query->where('user_id', $this->currentUser()->id);
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.leave.index', [
            'leaves' => $query->paginate(15),
            'canReview' => $canReview,
            'isTeam' => $isTeam,
            'statuses' => collect(LeaveStatus::cases())->mapWithKeys(fn (LeaveStatus $status) => [$status->value => $status->label()]),
        ])->layoutData(['heading' => 'Izin dan cuti'])->title('Izin dan cuti');
    }
}
