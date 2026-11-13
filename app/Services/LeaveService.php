<?php

namespace App\Services;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Mail\SendLeaveStatusEmail;
use App\Models\AttendanceDay;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Waktu;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    public function __construct(protected TenantClock $clock) {}

    public function submit(User $user, Tenant $tenant, array $data): LeaveRequest
    {
        $type = LeaveType::from($data['type']);
        $start = CarbonImmutable::parse($data['start_date']);
        $end = CarbonImmutable::parse($data['end_date']);

        if ($end->lt($start)) {
            throw ValidationException::withMessages(['end_date' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.']);
        }

        if ($start->lt($this->clock->today($tenant)->startOfDay())) {
            throw ValidationException::withMessages(['start_date' => 'Tanggal mulai tidak boleh sebelum hari ini.']);
        }

        if ($start->diffInDays($end) + 1 > 30) {
            throw ValidationException::withMessages(['end_date' => 'Rentang izin maksimal 30 hari.']);
        }

        $overlap = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', [LeaveStatus::Pending, LeaveStatus::Approved])
            ->where('start_date', '<=', $end->toDateString())
            ->where('end_date', '>=', $start->toDateString())
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages(['start_date' => 'Rentang ini bertumpang tindih dengan pengajuan lain.']);
        }

        if ($type === LeaveType::Cuti) {
            $hasWorkday = $this->workdaysInRange($user, $start, $end)->isNotEmpty();

            if (! $hasWorkday) {
                throw ValidationException::withMessages(['start_date' => 'Cuti hanya dapat diajukan pada hari kerja terjadwal.']);
            }
        }

        return LeaveRequest::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'type' => $type,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'reason' => trim($data['reason']),
            'status' => LeaveStatus::Pending,
        ]);
    }

    public function approve(LeaveRequest $leave, User $reviewer, ?string $note = null): void
    {
        if ($leave->status !== LeaveStatus::Pending) {
            throw ValidationException::withMessages(['review' => 'Pengajuan ini sudah diproses.']);
        }

        $range = [$leave->start_date->toDateString(), $leave->end_date->toDateString()];

        $alreadyCheckedIn = AttendanceDay::withoutGlobalScopes()
            ->where('tenant_id', $leave->tenant_id)
            ->where('user_id', $leave->user_id)
            ->whereBetween('work_date', $range)
            ->whereNotNull('check_in_at')
            ->exists();

        if ($alreadyCheckedIn) {
            throw ValidationException::withMessages(['review' => 'Ada hari dalam rentang ini yang sudah memiliki absen masuk.']);
        }

        DB::transaction(function () use ($leave, $reviewer, $note) {
            $leave->update([
                'status' => LeaveStatus::Approved,
                'reviewed_by_id' => $reviewer->id,
                'review_note' => $note,
                'reviewed_at' => now(),
            ]);

            $status = $leave->type->attendanceStatus();
            $cursor = CarbonImmutable::parse($leave->start_date);
            $end = CarbonImmutable::parse($leave->end_date);

            while ($cursor->lte($end)) {
                $date = $cursor->toDateString();
                $schedule = $this->scheduleFor($leave->user_id, $date);
                $isHoliday = $this->isHoliday($date);

                if ($leave->type === LeaveType::Cuti && (! $schedule || $schedule->is_day_off || $isHoliday)) {
                    $cursor = $cursor->addDay();

                    continue;
                }

                $existing = AttendanceDay::where('user_id', $leave->user_id)
                    ->where('work_date', $date)
                    ->first();

                if ($existing?->check_in_at) {
                    $cursor = $cursor->addDay();

                    continue;
                }

                AttendanceDay::updateOrCreate(
                    ['user_id' => $leave->user_id, 'work_date' => $date],
                    [
                        'tenant_id' => $leave->tenant_id,
                        'schedule_id' => $schedule?->id,
                        'leave_request_id' => $leave->id,
                        'status' => $status,
                        'flags' => [],
                    ],
                );

                $cursor = $cursor->addDay();
            }
        });

        $this->notifyStatus($leave, 'Disetujui');
    }

    public function reject(LeaveRequest $leave, User $reviewer, ?string $note = null): void
    {
        if ($leave->status !== LeaveStatus::Pending) {
            throw ValidationException::withMessages(['review' => 'Pengajuan ini sudah diproses.']);
        }

        $leave->update([
            'status' => LeaveStatus::Rejected,
            'reviewed_by_id' => $reviewer->id,
            'review_note' => $note,
            'reviewed_at' => now(),
        ]);

        $this->notifyStatus($leave, 'Ditolak');
    }

    protected function notifyStatus(LeaveRequest $leave, string $statusLabel): void
    {
        $leave->loadMissing(['user', 'tenant']);

        $range = $leave->start_date->eq($leave->end_date)
            ? Waktu::dateLong($leave->start_date->toDateString())
            : Waktu::dateLong($leave->start_date->toDateString()).' sampai '.Waktu::dateLong($leave->end_date->toDateString());

        Mail::to($leave->user->email)->queue(new SendLeaveStatusEmail(
            $leave->user->name,
            $leave->tenant->name,
            $leave->type->label(),
            $range,
            $statusLabel,
            $leave->review_note,
            route('app.leave.show', $leave->id),
        ));
    }

    public function cancel(LeaveRequest $leave, User $actor): void
    {
        $isOwner = $leave->user_id === $actor->id;
        $canReview = $actor->roleIn($leave->tenant)?->canReview() ?? false;

        if ($isOwner) {
            if ($leave->status !== LeaveStatus::Pending) {
                throw ValidationException::withMessages(['cancel' => 'Hanya pengajuan menunggu yang dapat dibatalkan.']);
            }
        } elseif (! $canReview) {
            abort(403);
        }

        if ($leave->status === LeaveStatus::Approved) {
            $today = $this->clock->today($leave->tenant)->toDateString();

            if ($leave->start_date->toDateString() < $today) {
                throw ValidationException::withMessages(['cancel' => 'Izin yang sudah berjalan tidak dapat dibatalkan. Ajukan koreksi pada hari presensi.']);
            }
        }

        if (in_array($leave->status, [LeaveStatus::Rejected, LeaveStatus::Cancelled], true)) {
            throw ValidationException::withMessages(['cancel' => 'Pengajuan ini sudah tidak aktif.']);
        }

        DB::transaction(function () use ($leave) {
            $days = AttendanceDay::where('leave_request_id', $leave->id)->get();

            // Hari yang sudah memiliki kejadian tidak boleh hilang riwayatnya.
            foreach ($days as $day) {
                if ($day->events()->exists() || $day->check_in_at !== null) {
                    throw ValidationException::withMessages(['cancel' => 'Izin ini sudah memiliki kejadian presensi sehingga tidak dapat dibatalkan.']);
                }
            }

            $leave->update([
                'status' => LeaveStatus::Cancelled,
                'cancelled_at' => now(),
            ]);

            foreach ($days as $day) {
                $day->delete();
            }
        });
    }

    /**
     * @return Collection<int, CarbonImmutable>
     */
    public function workdaysInRange(User $user, CarbonImmutable $start, CarbonImmutable $end)
    {
        $schedules = Schedule::where('user_id', $user->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->where('is_day_off', false)
            ->get();

        $holidays = Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn ($date) => CarbonImmutable::parse($date)->toDateString())
            ->all();

        return $schedules
            ->filter(fn (Schedule $schedule) => ! in_array($schedule->work_date->toDateString(), $holidays, true))
            ->map(fn (Schedule $schedule) => CarbonImmutable::parse($schedule->work_date))
            ->values();
    }

    protected function scheduleFor(int $userId, string $date): ?Schedule
    {
        return Schedule::where('user_id', $userId)->where('work_date', $date)->first();
    }

    protected function isHoliday(string $date): bool
    {
        return Holiday::where('date', $date)->exists();
    }
}
