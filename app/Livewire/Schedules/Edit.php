<?php

namespace App\Livewire\Schedules;

use App\Livewire\Concerns\WithTenant;
use App\Models\Schedule;
use App\Services\ScheduleService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Edit extends Component
{
    use WithTenant;

    public Schedule $schedule;

    public bool $isDayOff = false;

    public string $startTime = '';

    public string $endTime = '';

    public string $breakStart = '';

    public string $breakEnd = '';

    public string $note = '';

    public ?string $flash = null;

    public function mount(Schedule $schedule): void
    {
        Gate::authorize('update', $schedule);

        $this->ensureInTenant($schedule->tenant_id);

        $this->schedule = $schedule->load('user');
        $this->isDayOff = $schedule->is_day_off;
        $this->startTime = substr((string) ($schedule->start_time ?? ''), 0, 5);
        $this->endTime = substr((string) ($schedule->end_time ?? ''), 0, 5);
        $this->breakStart = substr((string) ($schedule->break_start ?? ''), 0, 5);
        $this->breakEnd = substr((string) ($schedule->break_end ?? ''), 0, 5);
        $this->note = $schedule->note ?? '';
    }

    public function save(ScheduleService $schedules): void
    {
        Gate::authorize('update', $this->schedule);

        $data = $this->validate([
            'isDayOff' => ['boolean'],
            'startTime' => [$this->isDayOff ? 'nullable' : 'required', 'date_format:H:i'],
            'endTime' => [$this->isDayOff ? 'nullable' : 'required', 'date_format:H:i', 'after:startTime'],
            'breakStart' => ['nullable', 'date_format:H:i'],
            'breakEnd' => ['nullable', 'date_format:H:i'],
            'note' => ['nullable', 'string', 'max:200'],
        ], [
            'endTime.after' => 'Jam selesai harus setelah jam mulai.',
        ]);

        $schedules->save($this->schedule, [
            'is_day_off' => $this->isDayOff,
            'start_time' => $this->startTime ?: null,
            'end_time' => $this->endTime ?: null,
            'break_start' => $this->breakStart ?: null,
            'break_end' => $this->breakEnd ?: null,
            'note' => $this->note ?: null,
        ]);

        $this->flash = 'Jadwal disimpan.';
        $this->schedule->refresh();
    }

    public function delete(ScheduleService $schedules): void
    {
        Gate::authorize('delete', $this->schedule);

        $schedules->deleteForDate($this->tenant(), $this->schedule->user, $this->schedule->work_date->toDateString());

        $this->redirect(route('app.schedules.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.schedules.edit', [
            'member' => $this->schedule->user,
        ])->layoutData(['heading' => 'Ubah jadwal'])->title('Ubah jadwal');
    }
}
