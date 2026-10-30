<?php

namespace App\Livewire\Attendance;

use App\Enums\CorrectionStatus;
use App\Livewire\Concerns\WithTenant;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceDay;
use App\Services\SelfieService;
use App\Services\TenantClock;
use App\Support\Waktu;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Show extends Component
{
    use WithTenant;

    public AttendanceDay $attendanceDay;

    public string $correctionCheckIn = '';

    public string $correctionCheckOut = '';

    public string $correctionNote = '';

    public string $correctionReason = '';

    public bool $showCorrectionForm = false;

    public ?string $flash = null;

    public function mount(AttendanceDay $attendanceDay): void
    {
        $this->ensureInTenant($attendanceDay->tenant_id);

        Gate::authorize('view', $attendanceDay);

        $this->attendanceDay = $attendanceDay->load(['user', 'schedule', 'location', 'leaveRequest', 'events' => fn ($q) => $q->orderBy('occurred_at'), 'corrections.requestedBy', 'corrections.reviewedBy']);

        $this->correctionCheckIn = $this->attendanceDay->check_in_at
            ? Waktu::local($this->attendanceDay->check_in_at, $this->tenant())->format('H:i')
            : '';
        $this->correctionCheckOut = $this->attendanceDay->check_out_at
            ? Waktu::local($this->attendanceDay->check_out_at, $this->tenant())->format('H:i')
            : '';
        $this->correctionNote = $this->attendanceDay->note ?? '';
    }

    public function openCorrection(): void
    {
        Gate::authorize('requestCorrection', $this->attendanceDay);

        if ($this->attendanceDay->corrections()->where('status', CorrectionStatus::Pending)->exists()) {
            $this->flash = 'Sudah ada koreksi menunggu tinjauan untuk hari ini.';

            return;
        }

        $this->showCorrectionForm = true;
    }

    public function submitCorrection(TenantClock $clock): void
    {
        Gate::authorize('requestCorrection', $this->attendanceDay);

        $this->validate([
            'correctionCheckIn' => ['nullable', 'date_format:H:i'],
            'correctionCheckOut' => ['nullable', 'date_format:H:i'],
            'correctionNote' => ['nullable', 'string', 'max:500'],
            'correctionReason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'correctionReason.required' => 'Alasan koreksi wajib diisi.',
            'correctionReason.min' => 'Tuliskan alasan minimal 10 karakter.',
        ]);

        if (! $this->correctionCheckIn && ! $this->correctionCheckOut) {
            $this->addError('correctionCheckIn', 'Isi minimal salah satu jam masuk atau pulang.');

            return;
        }

        $tenant = $this->tenant();
        $workDate = $this->attendanceDay->work_date->toDateString();

        $requested = [];

        if ($this->correctionCheckIn !== '') {
            $requested['check_in_at'] = $clock->atLocal($tenant, $workDate, $this->correctionCheckIn)->toIso8601String();
        }

        if ($this->correctionCheckOut !== '') {
            $requested['check_out_at'] = $clock->atLocal($tenant, $workDate, $this->correctionCheckOut)->toIso8601String();
        }

        if ($this->correctionNote !== '') {
            $requested['note'] = $this->correctionNote;
        }

        AttendanceCorrection::create([
            'tenant_id' => $tenant->id,
            'attendance_day_id' => $this->attendanceDay->id,
            'user_id' => $this->attendanceDay->user_id,
            'requested_by_id' => $this->currentUser()->id,
            'status' => CorrectionStatus::Pending,
            'original_values' => [
                'check_in_at' => $this->attendanceDay->check_in_at?->toIso8601String(),
                'check_out_at' => $this->attendanceDay->check_out_at?->toIso8601String(),
                'note' => $this->attendanceDay->note,
            ],
            'requested_values' => $requested,
            'reason' => $this->correctionReason,
        ]);

        $this->showCorrectionForm = false;
        $this->correctionReason = '';
        $this->flash = 'Koreksi terkirim. Peninjau akan memeriksa pengajuan ini.';
        $this->attendanceDay->refresh()->load(['corrections.requestedBy', 'corrections.reviewedBy']);
    }

    public function render(SelfieService $selfies): View
    {
        $canReview = $this->membership()->role->canReview();

        return view('livewire.attendance.show', [
            'day' => $this->attendanceDay,
            'events' => $this->attendanceDay->events->map(fn ($event) => [
                'event' => $event,
                'selfie' => $selfies->temporaryUrl($event->id),
            ]),
            'canRequestCorrection' => Gate::allows('requestCorrection', $this->attendanceDay),
            'canReview' => $canReview,
            'hasPendingCorrection' => $this->attendanceDay->corrections->contains(fn ($correction) => $correction->status === CorrectionStatus::Pending),
        ])->layoutData(['heading' => 'Detail presensi'])->title('Detail presensi');
    }
}
