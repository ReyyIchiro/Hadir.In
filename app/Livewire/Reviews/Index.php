<?php

namespace App\Livewire\Reviews;

use App\Enums\CorrectionStatus;
use App\Enums\LeaveStatus;
use App\Enums\MembershipRole;
use App\Enums\ReviewStatus;
use App\Livewire\Concerns\WithTenant;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceReview;
use App\Models\LeaveRequest;
use App\Models\Membership;
use App\Support\Waktu;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Index extends Component
{
    use WithTenant;

    public string $tab = 'presensi';

    public function mount(): void
    {
        Gate::authorize('viewAny', AttendanceReview::class);

        $tab = request()->query('tab');
        if (in_array($tab, ['presensi', 'koreksi', 'izin'], true)) {
            $this->tab = $tab;
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['presensi', 'koreksi', 'izin'], true)) {
            $this->tab = $tab;
        }
    }

    public function render(): View
    {
        $membership = $this->membership();
        $scopedUserIds = $this->scopedUserIds($membership);

        $reviews = AttendanceReview::with(['user', 'event.location'])
            ->where('status', ReviewStatus::Pending)
            ->when($scopedUserIds !== null, fn ($q) => $q->whereIn('user_id', $scopedUserIds))
            ->orderBy('created_at')
            ->get();

        $corrections = AttendanceCorrection::with(['user', 'attendanceDay'])
            ->where('status', CorrectionStatus::Pending)
            ->when($scopedUserIds !== null, fn ($q) => $q->whereIn('user_id', $scopedUserIds))
            ->orderBy('created_at')
            ->get();

        $leaves = LeaveRequest::with('user')
            ->where('status', LeaveStatus::Pending)
            ->when($scopedUserIds !== null, fn ($q) => $q->whereIn('user_id', $scopedUserIds))
            ->orderBy('created_at')
            ->get();

        return view('livewire.reviews.index', [
            'reviews' => $reviews,
            'corrections' => $corrections,
            'leaves' => $leaves,
            'counts' => [
                'presensi' => $reviews->count(),
                'koreksi' => $corrections->count(),
                'izin' => $leaves->count(),
            ],
        ])->layoutData(['heading' => 'Tinjauan'])->title('Tinjauan');
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
