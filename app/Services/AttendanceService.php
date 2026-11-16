<?php

namespace App\Services;

use App\Enums\AttendanceEventStatus;
use App\Enums\AttendanceEventType;
use App\Enums\LeaveStatus;
use App\Models\AttendanceDay;
use App\Models\AttendanceEvent;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\User;
use App\Support\AttendanceResult;
use App\Support\Flags;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function __construct(
        protected TenantClock $clock,
        protected GeofenceService $geofence,
        protected SelfieService $selfies,
    ) {}

    /**
     * @param  array{latitude?: ?float, longitude?: ?float, accuracy?: ?float, attempt_uuid?: ?string}  $input
     */
    public function checkIn(User $user, Tenant $tenant, array $input, ?UploadedFile $selfie = null): AttendanceResult
    {
        $settings = $tenant->settings;
        $now = $this->clock->now($tenant);
        $workDate = $now->toDateString();
        $attemptUuid = $this->attemptUuid($input);

        if ($existing = $this->existingAttempt($user, $attemptUuid)) {
            return $this->duplicateResult($existing);
        }

        $schedule = $this->scheduleFor($user, $workDate);
        $holiday = $this->holidayOn($workDate);
        $leave = $this->approvedLeaveOn($user, $workDate);

        $startTime = $schedule?->start_time ?: $settings->work_start;
        $endTime = $schedule?->end_time ?: $settings->work_end;
        $scheduledStart = $this->clock->localAt($tenant, $workDate, $startTime);
        $scheduledEnd = $this->clock->localAt($tenant, $workDate, $endTime);

        $flags = [];
        $reason = null;

        if ($leave) {
            $reason = 'izin_disetujui';
        } elseif ($schedule?->is_day_off && ! $holiday) {
            if (! $settings->allow_unscheduled_attendance) {
                $reason = 'tidak_ada_jadwal';
            } else {
                $flags[] = Flags::TANPA_JADWAL;
            }
        } elseif (! $schedule && ! $holiday) {
            if (! $settings->allow_unscheduled_attendance) {
                $reason = 'tidak_ada_jadwal';
            } else {
                $flags[] = Flags::TANPA_JADWAL;
            }
        }

        if ($holiday) {
            $flags[] = Flags::HARI_LIBUR;
        }

        if ($reason === null) {
            if ($now->lt($scheduledStart->subMinutes($settings->early_window_minutes))) {
                $reason = 'terlalu_awal';
            } elseif ($now->gt($scheduledEnd)) {
                $reason = 'jadwal_berakhir';
            }
        }

        if ($reason === null && $this->hasCheckedIn($user, $workDate)) {
            $reason = 'sudah_masuk';
        }

        $geo = null;

        if ($reason === null) {
            $geo = $this->geofence->validate(
                $tenant,
                isset($input['latitude']) ? (float) $input['latitude'] : null,
                isset($input['longitude']) ? (float) $input['longitude'] : null,
                isset($input['accuracy']) ? (float) $input['accuracy'] : null,
            );

            $flags = Flags::merge($flags, $geo['flags']);

            if (! $geo['accepted']) {
                $reason = $geo['reason'];
            }
        }

        if ($reason === null && $settings->require_selfie && $selfie === null) {
            $reason = 'selfie_wajib';
        }

        $selfiePath = null;

        if ($selfie !== null) {
            try {
                $selfiePath = $this->selfies->store($tenant, $selfie);
            } catch (ValidationException) {
                $reason ??= 'selfie_tidak_valid';
            }
        }

        $lateMinutes = 0;

        if ($reason === null) {
            $lateMinutes = $this->clock->lateMinutes($now, $scheduledStart, $settings->grace_minutes);
        }

        $accepted = $reason === null;
        $status = $accepted ? $this->clock->statusFor($lateMinutes) : null;

        $result = DB::transaction(function () use (
            $tenant, $user, $now, $workDate, $schedule, $geo, $flags, $reason, $accepted,
            $lateMinutes, $status, $selfiePath, $attemptUuid
        ) {
            if ($accepted) {
                $this->finalizePreviousOpenDay($user, $workDate);
            }

            $day = null;

            if ($accepted) {
                $day = AttendanceDay::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'work_date' => $workDate,
                    'schedule_id' => $schedule?->id,
                    'location_id' => ($geo['location'] ?? null)?->id,
                    'status' => $status,
                    'check_in_at' => $now->setTimezone('UTC'),
                    'late_minutes' => $lateMinutes,
                    'flags' => $flags,
                ]);
            }

            $event = AttendanceEvent::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'attendance_day_id' => $day?->id,
                'type' => AttendanceEventType::CheckIn,
                'status' => $accepted ? AttendanceEventStatus::Accepted : AttendanceEventStatus::Rejected,
                'work_date' => $workDate,
                'occurred_at' => $now->setTimezone('UTC'),
                'location_id' => ($geo['location'] ?? null)?->id,
                'latitude' => isset($input['latitude']) ? (float) $input['latitude'] : null,
                'longitude' => isset($input['longitude']) ? (float) $input['longitude'] : null,
                'accuracy_meters' => isset($input['accuracy']) ? (float) $input['accuracy'] : null,
                'distance_meters' => $geo['distance'] ?? null,
                'selfie_path' => $selfiePath,
                'reason' => $reason,
                'flags' => $flags,
                'attempt_uuid' => $attemptUuid,
            ]);

            return [$day, $event];
        });

        [$day, $event] = $result;

        return AttendanceResult::make(
            accepted: $accepted,
            event: $event,
            day: $day,
            status: $status,
            flags: $flags,
            reason: $reason,
            message: $this->messageFor($reason, $accepted, $lateMinutes, $scheduledStart, $tenant, (bool) $holiday),
        );
    }

    /**
     * @param  array{latitude?: ?float, longitude?: ?float, accuracy?: ?float, attempt_uuid?: ?string}  $input
     */
    public function checkOut(User $user, Tenant $tenant, array $input, ?UploadedFile $selfie = null): AttendanceResult
    {
        $settings = $tenant->settings;
        $now = $this->clock->now($tenant);
        $workDate = $now->toDateString();
        $attemptUuid = $this->attemptUuid($input);

        if ($existing = $this->existingAttempt($user, $attemptUuid)) {
            return $this->duplicateResult($existing);
        }

        $day = $this->openDayForCheckout($user, $workDate);

        $reason = null;
        $flags = [];

        if (! $day) {
            $reason = 'belum_absen_masuk';
        } elseif ($day->check_out_at !== null) {
            $reason = 'sudah_pulang';
        } elseif ($day->check_in_at !== null && $day->check_in_at->diffInMinutes($now, true) > 24 * 60) {
            $reason = 'melewati_batas_pulang';
        }

        $geo = null;

        if ($reason === null) {
            $geo = $this->geofence->validate(
                $tenant,
                isset($input['latitude']) ? (float) $input['latitude'] : null,
                isset($input['longitude']) ? (float) $input['longitude'] : null,
                isset($input['accuracy']) ? (float) $input['accuracy'] : null,
            );

            $flags = Flags::merge($flags, $geo['flags']);

            if (! $geo['accepted']) {
                $reason = $geo['reason'];
            }
        }

        if ($reason === null && $settings->require_selfie && $selfie === null) {
            $reason = 'selfie_wajib';
        }

        $selfiePath = null;

        if ($selfie !== null) {
            try {
                $selfiePath = $this->selfies->store($tenant, $selfie);
            } catch (ValidationException) {
                $reason ??= 'selfie_tidak_valid';
            }
        }

        $earlyLeave = 0;
        $accepted = $reason === null;

        if ($accepted && $day) {
            $schedule = $day->schedule;
            $endTime = $schedule?->end_time ?: $settings->work_end;
            $workDateOfDay = $day->work_date->toDateString();
            $scheduledEnd = $this->clock->localAt($tenant, $workDateOfDay, $endTime);

            if ($now->gt($scheduledEnd->addMinutes($settings->checkout_late_minutes))) {
                $flags[] = Flags::PULANG_TERLAMBAT;
            }

            $earlyLeave = $this->clock->earlyLeaveMinutes($now, $scheduledEnd);

            if ($earlyLeave > $settings->early_leave_minutes) {
                $flags[] = Flags::PULANG_CEPAT;
            } else {
                $earlyLeave = 0;
            }

            if ($this->holidayOn($workDateOfDay)) {
                $flags[] = Flags::HARI_LIBUR;
            }
        }

        $event = DB::transaction(function () use (
            $tenant, $user, $now, $workDate, $day, $geo, $flags, $reason, $accepted,
            $earlyLeave, $selfiePath, $attemptUuid, $input
        ) {
            if ($accepted && $day) {
                $day->update([
                    'check_out_at' => $now->setTimezone('UTC'),
                    'early_leave_minutes' => $earlyLeave,
                    'flags' => Flags::merge($day->flags, $flags),
                ]);
            }

            return AttendanceEvent::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'attendance_day_id' => $day?->id,
                'type' => AttendanceEventType::CheckOut,
                'status' => $accepted ? AttendanceEventStatus::Accepted : AttendanceEventStatus::Rejected,
                'work_date' => $day?->work_date->toDateString() ?? $workDate,
                'occurred_at' => $now->setTimezone('UTC'),
                'location_id' => ($geo['location'] ?? null)?->id,
                'latitude' => isset($input['latitude']) ? (float) $input['latitude'] : null,
                'longitude' => isset($input['longitude']) ? (float) $input['longitude'] : null,
                'accuracy_meters' => isset($input['accuracy']) ? (float) $input['accuracy'] : null,
                'distance_meters' => $geo['distance'] ?? null,
                'selfie_path' => $selfiePath,
                'reason' => $reason,
                'flags' => $flags,
                'attempt_uuid' => $attemptUuid,
            ]);
        });

        return AttendanceResult::make(
            accepted: $accepted,
            event: $event,
            day: $day?->fresh(),
            status: $day?->status,
            flags: $flags,
            reason: $reason,
            message: $this->messageFor($reason, $accepted, 0, null, $tenant, false, true),
            canRequestReview: false,
        );
    }

    public function acceptReviewedEvent(AttendanceEvent $event, User $reviewer): AttendanceDay
    {
        if ($event->status !== AttendanceEventStatus::Rejected || $event->type !== AttendanceEventType::CheckIn) {
            throw ValidationException::withMessages(['review' => 'Kejadian ini tidak dapat disetujui.']);
        }

        $tenant = $event->tenant;
        $workDate = $event->work_date->toDateString();

        $existing = AttendanceDay::where('user_id', $event->user_id)
            ->where('work_date', $workDate)
            ->first();

        if ($existing?->check_in_at) {
            throw ValidationException::withMessages(['review' => 'Hari ini sudah memiliki absen masuk.']);
        }

        $schedule = $this->scheduleFor($event->user, $workDate);
        $settings = $tenant->settings;
        $startTime = $schedule?->start_time ?: $settings->work_start;
        $scheduledStart = $this->clock->localAt($tenant, $workDate, $startTime);
        $lateMinutes = $this->clock->lateMinutes($event->occurred_at, $scheduledStart, $settings->grace_minutes);
        $status = $this->clock->statusFor($lateMinutes);

        return DB::transaction(function () use ($event, $tenant, $workDate, $schedule, $lateMinutes, $status) {
            $flags = Flags::merge($event->flags, [Flags::DITERIMA_MANUAL]);

            $day = AttendanceDay::create([
                'tenant_id' => $tenant->id,
                'user_id' => $event->user_id,
                'work_date' => $workDate,
                'schedule_id' => $schedule?->id,
                'location_id' => $event->location_id,
                'status' => $status,
                'check_in_at' => $event->occurred_at,
                'late_minutes' => $lateMinutes,
                'flags' => $flags,
            ]);

            $event->update([
                'status' => AttendanceEventStatus::Accepted,
                'attendance_day_id' => $day->id,
                'flags' => $flags,
            ]);

            return $day;
        });
    }

    public function applyCorrection(AttendanceDay $day, array $values): AttendanceDay
    {
        $tenant = $day->tenant;
        $settings = $tenant->settings;
        $flags = Flags::merge($day->flags, [Flags::DIKOREKSI]);

        $checkIn = $day->check_in_at;
        $checkOut = $day->check_out_at;

        if (array_key_exists('check_in_at', $values)) {
            $checkIn = $values['check_in_at'] ? CarbonImmutable::parse($values['check_in_at'])->setTimezone('UTC') : null;
        }

        if (array_key_exists('check_out_at', $values)) {
            $checkOut = $values['check_out_at'] ? CarbonImmutable::parse($values['check_out_at'])->setTimezone('UTC') : null;
        }

        if ($checkIn && $checkOut && $checkOut->lte($checkIn)) {
            throw ValidationException::withMessages(['check_out_at' => 'Jam pulang harus setelah jam masuk.']);
        }

        $lateMinutes = 0;
        $earlyLeave = 0;
        $status = $day->status;

        if ($checkIn) {
            $schedule = $day->schedule;
            $startTime = $schedule?->start_time ?: $settings->work_start;
            $endTime = $schedule?->end_time ?: $settings->work_end;
            $workDate = $day->work_date->toDateString();
            $lateMinutes = $this->clock->lateMinutes(
                $checkIn,
                $this->clock->localAt($tenant, $workDate, $startTime),
                $settings->grace_minutes,
            );
            $status = $this->clock->statusFor($lateMinutes);

            if ($checkOut) {
                $earlyLeave = $this->clock->earlyLeaveMinutes(
                    $checkOut,
                    $this->clock->localAt($tenant, $workDate, $endTime),
                );

                if ($earlyLeave <= $settings->early_leave_minutes) {
                    $earlyLeave = 0;
                }
            }
        }

        $day->update([
            'check_in_at' => $checkIn,
            'check_out_at' => $checkOut,
            'status' => $status,
            'late_minutes' => $lateMinutes,
            'early_leave_minutes' => $earlyLeave,
            'note' => array_key_exists('note', $values) ? $values['note'] : $day->note,
            'flags' => $flags,
            'is_corrected' => true,
        ]);

        return $day;
    }

    public function finalizeDay(AttendanceDay $day): void
    {
        if ($day->finalized_at !== null) {
            return;
        }

        $flags = $day->flags ?? [];

        if ($day->check_in_at && ! $day->check_out_at) {
            $flags = Flags::merge($flags, [Flags::FINAL_OTOMATIS, Flags::TANPA_PULANG]);
        }

        $day->update([
            'flags' => $flags,
            'finalized_at' => now(),
        ]);
    }

    public function finalizePreviousOpenDay(User $user, string $beforeDate): void
    {
        $open = AttendanceDay::where('user_id', $user->id)
            ->where('work_date', '<', $beforeDate)
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->whereNull('finalized_at')
            ->orderByDesc('work_date')
            ->first();

        if ($open) {
            $this->finalizeDay($open);
        }
    }

    public function scheduleFor(User $user, string $workDate): ?Schedule
    {
        return Schedule::where('user_id', $user->id)
            ->where('work_date', $workDate)
            ->first();
    }

    public function holidayOn(string $workDate): ?Holiday
    {
        return Holiday::where('date', $workDate)->first();
    }

    public function approvedLeaveOn(User $user, string $workDate): ?LeaveRequest
    {
        return LeaveRequest::where('user_id', $user->id)
            ->where('status', LeaveStatus::Approved)
            ->where('start_date', '<=', $workDate)
            ->where('end_date', '>=', $workDate)
            ->first();
    }

    public function hasCheckedIn(User $user, string $workDate): bool
    {
        return AttendanceDay::where('user_id', $user->id)
            ->where('work_date', $workDate)
            ->whereNotNull('check_in_at')
            ->exists();
    }

    public function openDayForCheckout(User $user, string $workDate): ?AttendanceDay
    {
        $today = AttendanceDay::where('user_id', $user->id)
            ->where('work_date', $workDate)
            ->whereNotNull('check_in_at')
            ->first();

        // Hari ini sudah ada, baik masih terbuka maupun sudah pulang. Pemanggil
        // yang memutuskan apakah ini absen pulang kedua.
        if ($today) {
            return $today;
        }

        return AttendanceDay::where('user_id', $user->id)
            ->where('work_date', '<', $workDate)
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->whereNull('finalized_at')
            ->orderByDesc('work_date')
            ->first();
    }

    protected function attemptUuid(array $input): string
    {
        $uuid = $input['attempt_uuid'] ?? null;

        if (is_string($uuid) && Str::isUuid($uuid)) {
            return $uuid;
        }

        return (string) Str::uuid();
    }

    protected function existingAttempt(User $user, string $uuid): ?AttendanceEvent
    {
        // Cakup pengguna: uuid yang dikirim klien tidak boleh membaca kejadian
        // milik anggota lain, bahkan pada organisasi yang sama.
        return AttendanceEvent::where('user_id', $user->id)
            ->where('attempt_uuid', $uuid)
            ->first();
    }

    protected function duplicateResult(AttendanceEvent $event): AttendanceResult
    {
        return AttendanceResult::make(
            accepted: $event->status === AttendanceEventStatus::Accepted,
            event: $event,
            day: $event->attendanceDay,
            status: $event->attendanceDay?->status,
            flags: $event->flags ?? [],
            reason: 'duplikat',
            message: 'Percobaan ini sudah tercatat sebelumnya.',
            canRequestReview: false,
        );
    }

    protected function messageFor(
        ?string $reason,
        bool $accepted,
        int $lateMinutes,
        ?CarbonImmutable $scheduledStart,
        Tenant $tenant,
        bool $holiday,
        bool $isCheckout = false,
    ): string {
        if ($accepted) {
            if ($isCheckout) {
                return 'Absen pulang tercatat.';
            }

            if ($lateMinutes > 0) {
                return 'Absen masuk tercatat, '.$lateMinutes.' menit setelah jam mulai.';
            }

            if ($holiday) {
                return 'Absen masuk tercatat pada hari libur.';
            }

            return 'Absen masuk tercatat tepat waktu.';
        }

        return match ($reason) {
            'terlalu_awal' => 'Belum masuk jendela absen. Coba lagi '.$this->minutesUntilWindow($scheduledStart, $tenant).' menit lagi.',
            'jadwal_berakhir' => 'Jadwal kerja hari ini sudah berakhir.',
            'di_luar_radius' => 'Anda berada di luar radius lokasi kerja.',
            'akurasi_tidak_dapat_dipercaya' => 'Akurasi lokasi melebihi 500 meter. Dekati jendela atau tempat terbuka, lalu coba lagi.',
            'lokasi_tidak_tersedia' => 'Lokasi tidak tersedia. Aktifkan GPS lalu coba lagi.',
            'selfie_wajib' => 'Selfie wajib diambil sebelum absen.',
            'selfie_tidak_valid' => 'Selfie tidak dapat diproses. Ambil ulang.',
            'sudah_masuk' => 'Anda sudah absen masuk hari ini.',
            'sudah_pulang' => 'Anda sudah absen pulang hari ini.',
            'belum_absen_masuk' => 'Belum ada absen masuk hari ini.',
            'melewati_batas_pulang' => 'Absen pulang melewati batas 24 jam sejak absen masuk.',
            'izin_disetujui' => 'Ada izin disetujui pada tanggal ini.',
            'tidak_ada_jadwal' => 'Tidak ada jadwal kerja hari ini.',
            'duplikat' => 'Percobaan ini sudah tercatat sebelumnya.',
            default => 'Presensi tidak dapat dicatat.',
        };
    }

    protected function minutesUntilWindow(?CarbonImmutable $scheduledStart, Tenant $tenant): int
    {
        if (! $scheduledStart) {
            return 0;
        }

        $now = $this->clock->now($tenant);
        $open = $scheduledStart->subMinutes($tenant->settings->early_window_minutes);

        return max(0, (int) ceil($now->diffInMinutes($open, false)));
    }
}
