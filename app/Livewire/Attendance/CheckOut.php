<?php

namespace App\Livewire\Attendance;

use App\Livewire\Concerns\WithTenant;
use App\Models\AttendanceDay;
use App\Models\Location;
use App\Services\AttendanceService;
use App\Services\TenantClock;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class CheckOut extends Component
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

    public ?AttendanceDay $day = null;

    public function mount(): void
    {
        $tenant = $this->tenant();
        $workDate = app(TenantClock::class)->today($tenant)->toDateString();

        $day = AttendanceDay::where('user_id', $this->currentUser()->id)
            ->where('work_date', $workDate)
            ->whereNotNull('check_in_at')
            ->first();

        if (! $day) {
            $this->redirect(route('app.attendance.index'), navigate: true);

            return;
        }

        if ($day->check_out_at) {
            $this->redirect(route('app.attendance.show', $day), navigate: true);

            return;
        }

        $this->day = $day;
        $this->attemptUuid = (string) Str::uuid();
    }

    public function submit(AttendanceService $attendance): void
    {
        $tenant = $this->tenant();
        $settings = $tenant->settings;

        $this->processing = true;
        $this->flash = null;

        try {
            $this->validate([
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'accuracy' => ['nullable', 'numeric', 'min:0'],
                'selfie' => [$settings->require_selfie ? 'required' : 'nullable', 'image', 'max:5120'],
            ], [
                'selfie.required' => 'Selfie wajib diambil sebelum absen.',
                'selfie.image' => 'Berkas harus berupa gambar.',
                'selfie.max' => 'Ukuran gambar maksimal 5 MB.',
            ]);

            $result = $attendance->checkOut(
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

            $this->flash = $result->message;
            $this->flashTone = $result->accepted ? 'success' : 'danger';

            if ($result->accepted && $result->day) {
                $this->redirect(route('app.attendance.show', $result->day), navigate: true);
            } else {
                $this->attemptUuid = (string) Str::uuid();
            }
        } finally {
            $this->processing = false;
        }
    }

    public function render(): View
    {
        $locations = Location::where('is_active', true)
            ->get(['id', 'name', 'latitude', 'longitude', 'radius_meters'])
            ->map(fn (Location $location) => [
                'name' => $location->name,
                'lat' => (float) $location->latitude,
                'lng' => (float) $location->longitude,
                'radius' => $location->radius_meters,
            ])
            ->values();

        return view('livewire.attendance.check-out', [
            'settings' => $this->tenant()->settings,
            'locations' => $locations,
            'day' => $this->day,
        ])->layoutData(['heading' => 'Absen pulang'])->title('Absen pulang');
    }
}
