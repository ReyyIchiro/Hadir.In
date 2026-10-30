<?php

namespace App\Livewire\Reviews;

use App\Enums\CorrectionStatus;
use App\Livewire\Concerns\WithTenant;
use App\Models\AttendanceCorrection;
use App\Services\AttendanceService;
use App\Services\TenantClock;
use App\Support\Waktu;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CorrectionReview extends Component
{
    use WithTenant;

    public AttendanceCorrection $correction;

    public string $reviewNote = '';

    public ?string $flash = null;

    public function mount(AttendanceCorrection $correction): void
    {
        Gate::authorize('view', $correction);

        $this->ensureInTenant($correction->tenant_id);

        $this->correction = $correction->load(['user', 'attendanceDay.schedule', 'requestedBy', 'reviewedBy']);
    }

    public function approve(AttendanceService $attendance, TenantClock $clock): void
    {
        Gate::authorize('decide', $this->correction);

        $day = $this->correction->attendanceDay;

        if (! $day) {
            $this->flash = 'Catatan presensi tidak ditemukan.';

            return;
        }

        try {
            $attendance->applyCorrection($day, $this->correction->requested_values ?? []);
        } catch (ValidationException $exception) {
            $this->flash = collect($exception->errors())->flatten()->first() ?? 'Koreksi tidak dapat diterapkan.';

            return;
        }

        $this->correction->update([
            'status' => CorrectionStatus::Approved,
            'reviewed_by_id' => $this->currentUser()->id,
            'review_note' => $this->reviewNote ?: null,
            'reviewed_at' => now(),
        ]);

        $this->redirect(route('app.attendance.show', $day), navigate: true);
    }

    public function reject(): void
    {
        Gate::authorize('decide', $this->correction);

        $this->validate([
            'reviewNote' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reviewNote.required' => 'Tuliskan alasan penolakan.',
            'reviewNote.min' => 'Alasan minimal 5 karakter.',
        ]);

        $this->correction->update([
            'status' => CorrectionStatus::Rejected,
            'reviewed_by_id' => $this->currentUser()->id,
            'review_note' => $this->reviewNote,
            'reviewed_at' => now(),
        ]);

        $this->flash = 'Koreksi ditolak.';
        $this->correction->refresh()->load(['user', 'attendanceDay', 'requestedBy', 'reviewedBy']);
    }

    public function render(): View
    {
        $day = $this->correction->attendanceDay;
        $tenant = $this->tenant();
        $requested = $this->correction->requested_values ?? [];

        $format = function (?string $iso) use ($tenant) {
            if (! $iso) {
                return '--:--';
            }

            return Waktu::local(\Carbon\CarbonImmutable::parse($iso), $tenant)->format('H.i');
        };

        return view('livewire.reviews.correction-review', [
            'day' => $day,
            'requestedCheckIn' => isset($requested['check_in_at']) ? $format($requested['check_in_at']) : null,
            'requestedCheckOut' => isset($requested['check_out_at']) ? $format($requested['check_out_at']) : null,
            'originalCheckIn' => $format($this->correction->original_values['check_in_at'] ?? null),
            'originalCheckOut' => $format($this->correction->original_values['check_out_at'] ?? null),
            'canDecide' => Gate::allows('decide', $this->correction),
        ])->layoutData(['heading' => 'Tinjau koreksi'])->title('Tinjau koreksi');
    }
}
