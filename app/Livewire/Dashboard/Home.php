<?php

namespace App\Livewire\Dashboard;

use App\Enums\AttendanceStatus;
use App\Enums\CorrectionStatus;
use App\Enums\LeaveStatus;
use App\Enums\ReviewStatus;
use App\Livewire\Concerns\WithTenant;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceDay;
use App\Models\AttendanceReview;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Services\TenantClock;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Home extends Component
{
    use WithTenant;

    public function render(TenantClock $clock): View
    {
        $tenant = $this->tenant();
        $membership = $this->membership();
        $user = $this->currentUser();
        $today = $clock->today($tenant);
        $workDate = $today->toDateString();

        $schedule = Schedule::where('user_id', $user->id)->where('work_date', $workDate)->first();
        $day = AttendanceDay::where('user_id', $user->id)->where('work_date', $workDate)->first();
        $leave = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', [LeaveStatus::Pending, LeaveStatus::Approved])
            ->where('start_date', '<=', $workDate)
            ->where('end_date', '>=', $workDate)
            ->first();

        $monthStart = $today->startOfMonth()->toDateString();
        $monthEnd = $today->endOfMonth()->toDateString();

        // Ringkasan bulan lewat agregat, bukan memuat semua hari ke memori.
        $counts = AttendanceDay::where('user_id', $user->id)
            ->whereBetween('work_date', [$monthStart, $monthEnd])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'hadir' => (int) ($counts[AttendanceStatus::Hadir->value] ?? 0),
            'terlambat' => (int) ($counts[AttendanceStatus::Terlambat->value] ?? 0),
            'izin' => (int) ($counts[AttendanceStatus::Izin->value] ?? 0)
                + (int) ($counts[AttendanceStatus::Sakit->value] ?? 0)
                + (int) ($counts[AttendanceStatus::Cuti->value] ?? 0),
            'absen' => (int) ($counts[AttendanceStatus::Absen->value] ?? 0),
        ];

        $canReview = $membership->role->canReview();

        $pendingReviews = $canReview
            ? AttendanceReview::where('status', ReviewStatus::Pending)->count()
            : 0;

        $pendingCorrections = $canReview
            ? AttendanceCorrection::where('status', CorrectionStatus::Pending)->count()
            : 0;

        $pendingLeave = $canReview
            ? LeaveRequest::where('status', LeaveStatus::Pending)->count()
            : 0;

        $teamToday = null;

        if ($canReview) {
            $teamToday = $this->teamToday($tenant->id, $workDate);
        }

        return view('livewire.dashboard.home', [
            'today' => $today,
            'schedule' => $schedule,
            'day' => $day,
            'leave' => $leave,
            'stats' => $stats,
            'canReview' => $canReview,
            'pendingReviews' => $pendingReviews,
            'pendingCorrections' => $pendingCorrections,
            'pendingLeave' => $pendingLeave,
            'teamToday' => $teamToday,
        ])->layoutData(['heading' => 'Beranda'])->title('Beranda');
    }

    /**
     * @return array{scheduled: int, checked_in: int, absent: int}
     */
    protected function teamToday(int $tenantId, string $workDate): array
    {
        $schedules = Schedule::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('work_date', $workDate)
            ->where('is_day_off', false)
            ->count();

        $checkedIn = AttendanceDay::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('work_date', $workDate)
            ->whereNotNull('check_in_at')
            ->count();

        return [
            'scheduled' => $schedules,
            'checked_in' => $checkedIn,
            'absent' => max(0, $schedules - $checkedIn),
        ];
    }
}
