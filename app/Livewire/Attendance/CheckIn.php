<?php

namespace App\Livewire\Attendance;

use App\Enums\ReviewStatus;
use App\Livewire\Concerns\WithTenant;
use App\Models\AttendanceDay;
use App\Models\AttendanceEvent;
use App\Models\Location;
use App\Services\AttendanceService;
use App\Services\TenantClock;
use App\Support\AttendanceResult;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class CheckIn extends Component
{
    use WithFileUploads;
    use WithTenant;

    public ?string $attemptUuid = null;

    public ?float $latitude = null;

    public ?float $longitude = null;

    public ?float $accuracy = null;

    public $selfie = null;

    public bool $processing = false;

    public ?string $flash = null;

    public ?string $flashTone = 'neutral';

    public bool $canRequestReview = false;

    public ?int $rejectedEventId = null;

    public function mount(): void
    {
        $tenant = $this->tenant();
        $clock = app(TenantClock::class);
        $workDate = $clock->today($tenant)->toDateString();

        $day = AttendanceDay::where('user_id', $this->currentUser()->id)
            ->where('work_date', $workDate)
            ->first();

        if ($day?->check_in_at) {
            $this->redirect(route('app.attendance.show', $day), navigate: true);

            return;
        }

        $this->attemptUuid = (string) Str::uuid();
    }

    public function updatedSelfie(): void
    {
        $this->validate([
            'selfie' => ['nullable', 'image', 'max:12288'],
        ], [
            'selfie.image' => 'Berkas harus berupa gambar.',
            'selfie.max' => 'Ukuran gambar maksimal 12 MB.',
        ]);
    }

    public function submit(AttendanceService $attendance): void
    {
        $tenant = $this->tenant();
        $settings = $tenant->settings;

        $this->processing = true;
        $this->flash = null;
        $this->canRequestReview = false;

        try {
            $this->validate([
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'accuracy' => ['nullable', 'numeric', 'min:0'],
                'selfie' => [$settings->require_selfie ? 'required' : 'nullable', 'image', 'max:12288'],
            ], [
                'selfie.required' => 'Selfie wajib diambil sebelum absen.',
                'selfie.image' => 'Berkas harus berupa gambar.',
                'selfie.max' => 'Ukuran gambar maksimal 12 MB.',
            ]);

            $result = $attendance->checkIn(
                $this->currentUser(),
                $tenant,
                [
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                    'accuracy' => $this->accuracy,
                    'attempt_uuid' => $this->attemptUuid,
                ],
                $this->selfie,
            );

            $this->afterResult($result);
        } finally {
            $this->processing = false;
        }
    }

    protected function afterResult(AttendanceResult $result): void
    {
        $this->flash = $result->message;
        $this->flashTone = $result->accepted ? 'success' : 'danger';

        if ($result->accepted) {
            $this->canRequestReview = false;
            $this->dispatch('attendance-recorded');

            if ($result->day) {
                $this->redirect(route('app.attendance.show', $result->day), navigate: true);
            }

            return;
        }

        $this->canRequestReview = $result->canRequestReview;
        $this->rejectedEventId = $result->event->id;
        $this->attemptUuid = (string) Str::uuid();
    }

    public function requestReview(): void
    {
        if (! $this->rejectedEventId) {
            return;
        }

        $event = AttendanceEvent::where('user_id', $this->currentUser()->id)->find($this->rejectedEventId);

        if (! $event) {
            return;
        }

        if ($event->review()->exists()) {
            $this->flash = 'Pengajuan tinjauan sudah dikirim sebelumnya.';
            $this->flashTone = 'neutral';

            return;
        }

        $event->review()->create([
            'tenant_id' => $event->tenant_id,
            'user_id' => $event->user_id,
            'status' => ReviewStatus::Pending,
            'note' => 'Mohon tinjau absen masuk di luar radius.',
        ]);

        $this->canRequestReview = false;
        $this->flash = 'Pengajuan tinjauan terkirim. Peninjau akan memeriksa catatan ini.';
        $this->flashTone = 'neutral';
    }

    public function render(): View
    {
        $tenant = $this->tenant();
        $settings = $tenant->settings;

        $locations = Location::where('is_active', true)
            ->get(['id', 'name', 'latitude', 'longitude', 'radius_meters'])
            ->map(fn (Location $location) => [
                'name' => $location->name,
                'lat' => (float) $location->latitude,
                'lng' => (float) $location->longitude,
                'radius' => $location->radius_meters,
            ])
            ->values();

        return view('livewire.attendance.check-in', [
            'settings' => $settings,
            'locations' => $locations,
        ])->layoutData(['heading' => 'Absen masuk'])->title('Absen masuk');
    }
}
