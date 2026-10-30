<?php

namespace App\Livewire\Reviews;

use App\Enums\ReviewStatus;
use App\Livewire\Concerns\WithTenant;
use App\Models\AttendanceReview as ReviewModel;
use App\Services\AttendanceService;
use App\Services\SelfieService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class AttendanceReview extends Component
{
    use WithTenant;

    public ReviewModel $review;

    public string $reviewNote = '';

    public ?string $flash = null;

    public function mount(ReviewModel $review): void
    {
        Gate::authorize('view', $review);

        $this->ensureInTenant($review->tenant_id);

        $this->review = $review->load(['user', 'event.location', 'event.attendanceDay']);
    }

    public function approve(AttendanceService $attendance): void
    {
        Gate::authorize('decide', $this->review);

        try {
            $day = $attendance->acceptReviewedEvent($this->review->event, $this->currentUser());
        } catch (ValidationException $exception) {
            $this->flash = $exception->errors()['review'][0] ?? 'Tinjauan tidak dapat disetujui.';

            return;
        }

        $this->review->update([
            'status' => ReviewStatus::Approved,
            'reviewer_id' => $this->currentUser()->id,
            'review_note' => $this->reviewNote ?: null,
            'reviewed_at' => now(),
        ]);

        $this->redirect(route('app.attendance.show', $day), navigate: true);
    }

    public function reject(): void
    {
        Gate::authorize('decide', $this->review);

        $this->validate([
            'reviewNote' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reviewNote.required' => 'Tuliskan alasan penolakan.',
            'reviewNote.min' => 'Alasan minimal 5 karakter.',
        ]);

        $this->review->update([
            'status' => ReviewStatus::Rejected,
            'reviewer_id' => $this->currentUser()->id,
            'review_note' => $this->reviewNote,
            'reviewed_at' => now(),
        ]);

        $this->flash = 'Tinjauan ditolak.';
        $this->review->refresh()->load(['user', 'event.location']);
    }

    public function render(SelfieService $selfies): View
    {
        return view('livewire.reviews.attendance-review', [
            'event' => $this->review->event,
            'selfie' => $selfies->temporaryUrl($this->review->event?->id),
            'canDecide' => Gate::allows('decide', $this->review),
        ])->layoutData(['heading' => 'Tinjau absen'])->title('Tinjau absen');
    }
}
