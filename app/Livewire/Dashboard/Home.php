<?php

namespace App\Livewire\Dashboard;

use App\Enums\AttendanceStatus;
use App\Enums\LeaveStatus;
use App\Enums\MembershipRole;
use App\Livewire\Concerns\WithTenant;
use App\Models\AttendanceDay;
use App\Models\LeaveRequest;
use App\Models\Membership;
use App\Models\Schedule;
use App\Services\TenantClock;
use App\Support\Flags;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
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

        $monthDays = AttendanceDay::where('user_id', $user->id)
            ->whereBetween('work_date', [$monthStart, $monthEnd])
            ->get();

        $stats = [
            'hadir' => $monthDays->filter(fn (AttendanceDay $d) => $d->status === AttendanceStatus::Hadir)->count(),
            'terlambat' => $monthDays->filter(fn (AttendanceDay $d) => $d->status === AttendanceStatus::Terlambat)->count(),
            'izin' => $monthDays->filter(fn (AttendanceDay $d) => in_array($d->status, [AttendanceStatus::Izin, AttendanceStatus::Sakit, AttendanceStatus::Cuti], true))->count(),
            'absen' => $monthDays->filter(fn (AttendanceDay $d) => $d->status === AttendanceStatus::Absen)->count(),
        ];

        $canReview = $membership->role->canReview();

        $pendingReviews = $canReview
            ? \App\Models\AttendanceReview::where('status', \App\Enums\ReviewStatus::Pending)->count()
            : 0;

        $pendingCorrections = $canReview
            ? \App\Models\AttendanceCorrection::where('status', \App\Enums\CorrectionStatus::Pending)->count()
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
