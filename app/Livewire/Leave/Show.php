<?php

namespace App\Livewire\Leave;

use App\Livewire\Concerns\WithTenant;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Show extends Component
{
    use WithTenant;

    public LeaveRequest $leave;

    public string $reviewNote = '';

    public ?string $flash = null;

    public function mount(LeaveRequest $leaveRequest): void
    {
        Gate::authorize('view', $leaveRequest);

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

        $this->leave->refresh()->load(['reviewedBy']);
        $this->flash = 'Pengajuan disetujui.';
        $this->reviewNote = '';
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

        $this->leave->refresh()->load(['reviewedBy']);
        $this->flash = 'Pengajuan ditolak.';
        $this->reviewNote = '';
    }

    public function cancel(LeaveService $leaves): void
    {
        Gate::authorize('cancel', $this->leave);

        $leaves->cancel($this->leave, $this->currentUser());

        $this->leave->refresh()->load(['reviewedBy']);
        $this->flash = 'Pengajuan dibatalkan.';
    }

    public function render(): View
    {
        return view('livewire.leave.show', [
            'canReview' => Gate::allows('review', $this->leave),
            'canCancel' => Gate::allows('cancel', $this->leave),
        ])->layoutData(['heading' => 'Detail pengajuan'])->title('Detail pengajuan');
    }
}
