<?php

namespace App\Livewire\Reviews;

use App\Enums\LeaveStatus;
use App\Livewire\Concerns\WithTenant;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class LeaveReview extends Component
{
    use WithTenant;

    public LeaveRequest $leave;

    public string $reviewNote = '';

    public ?string $flash = null;

    public function mount(LeaveRequest $leaveRequest): void
    {
        Gate::authorize('review', $leaveRequest);

        $this->ensureInTenant($leaveRequest->tenant_id);

        $this->leave = $leaveRequest->load(['user', 'reviewedBy']);
    }

    public function approve(LeaveService $leaves): void
    {
        Gate::authorize('review', $this->leave);

        try {
            $leaves->approve($this->leave, $this->currentUser(), $this->reviewNote ?: null);
        } catch (ValidationException $exception) {
            $this->flash = $exception->errors()['review'][0] ?? 'Pengajuan tidak dapat disetujui.';

            return;
        }

        $this->redirect(route('app.leave.show', $this->leave), navigate: true);
    }

    public function reject(LeaveService $leaves): void
    {
        Gate::authorize('review', $this->leave);

        $this->validate([
            'reviewNote' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reviewNote.required' => 'Tuliskan alasan penolakan.',
            'reviewNote.min' => 'Alasan minimal 5 karakter.',
        ]);

        try {
            $leaves->reject($this->leave, $this->currentUser(), $this->reviewNote);
        } catch (ValidationException $exception) {
            $this->flash = $exception->errors()['review'][0] ?? 'Pengajuan tidak dapat ditolak.';

            return;
        }

        $this->flash = 'Pengajuan ditolak.';
        $this->leave->refresh()->load(['user', 'reviewedBy']);
    }

    public function render(): View
    {
        return view('livewire.reviews.leave-review', [
            'canDecide' => Gate::allows('review', $this->leave) && $this->leave->status === LeaveStatus::Pending,
        ])->layoutData(['heading' => 'Tinjau izin'])->title('Tinjau izin');
    }
}
