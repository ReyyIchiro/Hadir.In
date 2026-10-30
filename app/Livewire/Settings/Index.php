<?php

namespace App\Livewire\Settings;

use App\Livewire\Concerns\WithTenant;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Index extends Component
{
    use WithTenant;

    public string $work_start = '08:00';

    public string $work_end = '17:00';

    public string $break_start = '';

    public string $break_end = '';

    public string $early_window_minutes = '60';

    public string $grace_minutes = '10';

    public string $checkout_late_minutes = '240';

    public string $early_leave_minutes = '10';

    public string $geofence_buffer_meters = '50';

    public string $low_accuracy_meters = '100';

    public string $gps_timeout_seconds = '15';

    public string $selfie_retention_days = '90';

    public bool $allow_unscheduled_attendance = false;

    public bool $require_location = true;

    public bool $require_selfie = true;

    public ?string $flash = null;

    public function mount(): void
    {
        Gate::authorize('manage-settings', $this->membership());

        $settings = $this->tenant()->settings;

        $this->work_start = substr((string) $settings->work_start, 0, 5);
        $this->work_end = substr((string) $settings->work_end, 0, 5);
        $this->break_start = $settings->break_start ? substr((string) $settings->break_start, 0, 5) : '';
        $this->break_end = $settings->break_end ? substr((string) $settings->break_end, 0, 5) : '';
        $this->early_window_minutes = (string) $settings->early_window_minutes;
        $this->grace_minutes = (string) $settings->grace_minutes;
        $this->checkout_late_minutes = (string) $settings->checkout_late_minutes;
        $this->early_leave_minutes = (string) $settings->early_leave_minutes;
        $this->geofence_buffer_meters = (string) $settings->geofence_buffer_meters;
        $this->low_accuracy_meters = (string) $settings->low_accuracy_meters;
        $this->gps_timeout_seconds = (string) $settings->gps_timeout_seconds;
        $this->selfie_retention_days = (string) $settings->selfie_retention_days;
        $this->allow_unscheduled_attendance = $settings->allow_unscheduled_attendance;
        $this->require_location = $settings->require_location;
        $this->require_selfie = $settings->require_selfie;
    }

    public function save(): void
    {
        Gate::authorize('manage-settings', $this->membership());

        $data = $this->validate([
            'work_start' => ['required', 'date_format:H:i'],
            'work_end' => ['required', 'date_format:H:i'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end' => ['nullable', 'date_format:H:i'],
            'early_window_minutes' => ['required', 'integer', 'min:0', 'max:180'],
            'grace_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'checkout_late_minutes' => ['required', 'integer', 'min:0', 'max:720'],
            'early_leave_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'geofence_buffer_meters' => ['required', 'integer', 'min:0', 'max:500'],
            'low_accuracy_meters' => ['required', 'integer', 'min:10', 'max:1000'],
            'gps_timeout_seconds' => ['required', 'integer', 'min:5', 'max:60'],
            'selfie_retention_days' => ['required', 'integer', 'min:7', 'max:365'],
            'allow_unscheduled_attendance' => ['boolean'],
            'require_location' => ['boolean'],
            'require_selfie' => ['boolean'],
        ], [
            'work_start.required' => 'Jam mulai kerja wajib diisi.',
            'work_end.required' => 'Jam selesai kerja wajib diisi.',
            'work_start.date_format' => 'Format jam memakai HH:MM.',
            'work_end.date_format' => 'Format jam memakai HH:MM.',
        ]);

        if ($this->work_end === $this->work_start) {
            $this->addError('work_end', 'Jam selesai tidak boleh sama dengan jam mulai.');

            return;
        }

        if ($this->break_start !== '' && $this->break_end === '') {
            $this->addError('break_end', 'Jam selesai istirahat wajib diisi.');

            return;
        }

        if ($this->break_end !== '' && $this->break_start === '') {
            $this->addError('break_start', 'Jam mulai istirahat wajib diisi.');

            return;
        }

        $this->tenant()->settings->update([
            'work_start' => $data['work_start'].':00',
            'work_end' => $data['work_end'].':00',
            'break_start' => $this->break_start !== '' ? $this->break_start.':00' : null,
            'break_end' => $this->break_end !== '' ? $this->break_end.':00' : null,
            'early_window_minutes' => (int) $data['early_window_minutes'],
            'grace_minutes' => (int) $data['grace_minutes'],
            'checkout_late_minutes' => (int) $data['checkout_late_minutes'],
            'early_leave_minutes' => (int) $data['early_leave_minutes'],
            'geofence_buffer_meters' => (int) $data['geofence_buffer_meters'],
            'low_accuracy_meters' => (int) $data['low_accuracy_meters'],
            'gps_timeout_seconds' => (int) $data['gps_timeout_seconds'],
            'selfie_retention_days' => (int) $data['selfie_retention_days'],
            'allow_unscheduled_attendance' => $this->allow_unscheduled_attendance,
            'require_location' => $this->require_location,
            'require_selfie' => $this->require_selfie,
        ]);

        $this->flash = 'Pengaturan disimpan. Perubahan berlaku untuk absen berikutnya.';
    }

    public function render(): View
    {
        return view('livewire.settings.index', [
            'tenant' => $this->tenant(),
        ])->layoutData(['heading' => 'Pengaturan'])->title('Pengaturan');
    }
}
