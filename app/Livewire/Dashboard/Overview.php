<?php

namespace App\Livewire\Dashboard;

use App\Enums\AttendanceStatus;
use App\Enums\CorrectionStatus;
use App\Enums\LeaveStatus;
use App\Enums\MembershipRole;
use App\Enums\ReviewStatus;
use App\Livewire\Concerns\WithTenant;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceDay;
use App\Models\AttendanceReview;
use App\Models\LeaveRequest;
use App\Models\Membership;
use App\Models\Schedule;
use App\Services\TenantClock;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Overview extends Component
{
    use WithTenant;

    public function render(TenantClock $clock): View
    {
        $tenant = $this->tenant();
        $membership = $this->membership();

        Gate::authorize('view-reports', $membership);

        $today = $clock->today($tenant);
        $workDate = $today->toDateString();
        $scope = $this->scopedUserIds($membership);

        $scheduled = Schedule::where('work_date', $workDate)
            ->where('is_day_off', false)
            ->when($scope !== null, fn ($q) => $q->whereIn('user_id', $scope))
            ->count();

        $dayQuery = AttendanceDay::where('work_date', $workDate)
            ->when($scope !== null, fn ($q) => $q->whereIn('user_id', $scope));

        $checkedIn = (clone $dayQuery)->whereNotNull('check_in_at')->count();
        $late = (clone $dayQuery)->where('status', AttendanceStatus::Terlambat)->count();
        $onLeave = LeaveRequest::where('status', LeaveStatus::Approved)
            ->where('start_date', '<=', $workDate)
            ->where('end_date', '>=', $workDate)
            ->when($scope !== null, fn ($q) => $q->whereIn('user_id', $scope))
            ->count();

        $pendingReviews = AttendanceReview::where('status', ReviewStatus::Pending)
            ->when($scope !== null, fn ($q) => $q->whereIn('user_id', $scope))
            ->count();

        $pendingCorrections = AttendanceCorrection::where('status', CorrectionStatus::Pending)
            ->when($scope !== null, fn ($q) => $q->whereIn('user_id', $scope))
            ->count();

        $pendingLeave = LeaveRequest::where('status', LeaveStatus::Pending)
            ->when($scope !== null, fn ($q) => $q->whereIn('user_id', $scope))
            ->count();

        $recent = AttendanceDay::with(['user', 'location'])
            ->orderByDesc('check_in_at')
            ->when($scope !== null, fn ($q) => $q->whereIn('user_id', $scope))
            ->limit(8)
            ->get();

        return view('livewire.dashboard.overview', [
            'today' => $today,
            'stats' => [
                'scheduled' => $scheduled,
                'checked_in' => $checkedIn,
                'late' => $late,
                'on_leave' => $onLeave,
                'absent' => max(0, $scheduled - $checkedIn - $onLeave),
            ],
            'pending' => [
                'reviews' => $pendingReviews,
                'corrections' => $pendingCorrections,
                'leave' => $pendingLeave,
            ],
            'recent' => $recent,
        ])->layoutData(['heading' => 'Ringkasan'])->title('Ringkasan');
    }

    /**
     * @return array<int, int>|null
     */
    protected function scopedUserIds(Membership $membership): ?array
    {
        if ($membership->role !== MembershipRole::Reviewer) {
            return null;
        }

        if (! $membership->primary_location_id) {
            return [];
        }

        return Membership::where('tenant_id', $membership->tenant_id)
            ->where('primary_location_id', $membership->primary_location_id)
            ->where('is_active', true)
            ->pluck('user_id')
            ->all();
    }
}
