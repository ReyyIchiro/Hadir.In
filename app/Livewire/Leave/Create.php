<?php

namespace App\Livewire\Leave;

use App\Enums\LeaveType;
use App\Livewire\Concerns\WithTenant;
use App\Services\LeaveService;
use App\Services\TenantClock;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Create extends Component
{
    use WithTenant;

    public string $type = 'izin';

    public string $startDate = '';

    public string $endDate = '';

    public string $reason = '';

    public function mount(): void
    {
        $today = app(TenantClock::class)->today($this->tenant());
        $this->startDate = $today->toDateString();
        $this->endDate = $today->toDateString();
    }

    public function submit(LeaveService $leaves): void
    {
        $data = $this->validate([
            'type' => ['required', 'in:izin,sakit,cuti'],
            'startDate' => ['required', 'date'],
            'endDate' => ['required', 'date', 'after_or_equal:startDate'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'type.required' => 'Pilih jenis pengajuan.',
            'startDate.required' => 'Tanggal mulai wajib diisi.',
            'endDate.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'reason.required' => 'Alasan wajib diisi.',
            'reason.min' => 'Tuliskan alasan minimal 5 karakter.',
        ]);

        try {
            $leave = $leaves->submit($this->currentUser(), $this->tenant(), [
                'type' => $data['type'],
                'start_date' => $data['startDate'],
                'end_date' => $data['endDate'],
                'reason' => $data['reason'],
            ]);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError(match ($field) {
                    'start_date' => 'startDate',
                    'end_date' => 'endDate',
                    'type' => 'type',
                    default => 'reason',
                }, $messages[0]);
            }

            return;
        }

        $this->redirect(route('app.leave.show', $leave), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.leave.create', [
            'types' => collect(LeaveType::cases())->mapWithKeys(fn (LeaveType $type) => [$type->value => $type->label()]),
        ])->layoutData(['heading' => 'Ajukan izin'])->title('Ajukan izin');
    }
}
