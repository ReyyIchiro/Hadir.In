<?php

namespace App\Livewire\Schedules;

use App\Livewire\Concerns\WithTenant;
use App\Models\Membership;
use App\Services\ScheduleService;
use App\Services\TenantClock;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class BulkApply extends Component
{
    use WithTenant;

    public array $userIds = [];

    public string $startDate = '';

    public string $endDate = '';

    public array $weekdays = [1, 2, 3, 4, 5];

    public string $startTime = '08:00';

    public string $endTime = '17:00';

    public string $breakStart = '12:00';

    public string $breakEnd = '13:00';

    public string $mode = 'skip';

    public string $note = '';

    public ?array $preview = null;

    public ?string $flash = null;

    public function mount(): void
    {
        Gate::authorize('manage-schedules', $this->membership());

        $today = app(TenantClock::class)->today($this->tenant());
        $this->startDate = $today->toDateString();
        $this->endDate = $today->addDays(30)->toDateString();

        $member = request()->integer('member');
        if ($member) {
            $this->userIds = [$member];
        }

        $date = request()->query('date');
        if (is_string($date) && $date !== '') {
            $this->startDate = $date;
            $this->endDate = $date;
        }

        $this->refreshPreview();
    }

    /**
     * Hitung ulang ringkasan. Dipakai saat mount dan saat tombol "Perbarui
     * ringkasan" ditekan. Layanan diambil dari container supaya bisa dipanggil
     * dari mount, di mana Livewire belum menyuntikkan dependensi aksi.
     */
    protected function refreshPreview(): void
    {
        $this->preview = app(ScheduleService::class)->preview($this->tenant(), [
            'user_ids' => $this->userIds,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'weekdays' => $this->weekdays,
            'mode' => $this->mode,
        ]);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['userIds', 'startDate', 'endDate', 'weekdays', 'mode'], true)) {
            $this->preview = null;
        }
    }

    /**
     * Nama aksi sengaja bukan "preview" karena properti publik $preview
     * menutupi method dengan nama yang sama di Livewire, sehingga tombol
     * tidak pernah memanggil method-nya.
     */
    public function refreshSummary(): void
    {
        $this->validate([
            'userIds' => ['array'],
            'startDate' => ['required', 'date'],
            'endDate' => ['required', 'date', 'after_or_equal:startDate'],
            'weekdays' => ['array'],
        ]);

        $this->refreshPreview();
    }

    public function apply(ScheduleService $schedules): void
    {
        Gate::authorize('manage-schedules', $this->membership());

        $data = $this->validate([
            'userIds' => ['required', 'array', 'min:1'],
            'userIds.*' => ['integer'],
            'startDate' => ['required', 'date'],
            'endDate' => ['required', 'date', 'after_or_equal:startDate'],
            'weekdays' => ['required', 'array', 'min:1'],
            'weekdays.*' => ['integer', 'between:1,7'],
            'startTime' => ['required', 'date_format:H:i'],
            'endTime' => ['required', 'date_format:H:i', 'after:startTime'],
            'breakStart' => ['nullable', 'date_format:H:i'],
            'breakEnd' => ['nullable', 'date_format:H:i'],
            'mode' => ['required', 'in:skip,overwrite'],
            'note' => ['nullable', 'string', 'max:200'],
        ], [
            'userIds.required' => 'Pilih minimal satu anggota.',
            'weekdays.required' => 'Pilih minimal satu hari dalam minggu.',
            'endTime.after' => 'Jam selesai harus setelah jam mulai.',
        ]);

        $result = $schedules->bulkApply($this->tenant(), [
            'user_ids' => $data['userIds'],
            'start_date' => $data['startDate'],
            'end_date' => $data['endDate'],
            'weekdays' => $data['weekdays'],
            'start_time' => $data['startTime'],
            'end_time' => $data['endTime'],
            'break_start' => $data['breakStart'] ?: null,
            'break_end' => $data['breakEnd'] ?: null,
            'mode' => $data['mode'],
            'note' => $data['note'] ?: null,
        ]);

        $this->flash = "Jadwal diterapkan: {$result['created']} dibuat, {$result['updated']} diperbarui, {$result['skipped']} dilewati.";
        $this->preview = null;
    }

    public function render(): View
    {
        return view('livewire.schedules.bulk-apply', [
            'members' => Membership::where('is_active', true)
                ->with('user')
                ->get()
                ->filter(fn (Membership $m) => $m->user !== null)
                ->sortBy(fn (Membership $m) => $m->user->name),
        ])->layoutData(['heading' => 'Terapkan jadwal'])->title('Terapkan jadwal');
    }
}
