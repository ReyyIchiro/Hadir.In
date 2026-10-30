<?php

namespace App\Models;

use App\Casts\DateOnly;
use App\Enums\AttendanceStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceDay extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'work_date',
        'schedule_id',
        'location_id',
        'leave_request_id',
        'status',
        'check_in_at',
        'check_out_at',
        'late_minutes',
        'early_leave_minutes',
        'flags',
        'note',
        'is_corrected',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => DateOnly::class,
            'status' => AttendanceStatus::class,
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'late_minutes' => 'integer',
            'early_leave_minutes' => 'integer',
            'flags' => 'array',
            'is_corrected' => 'boolean',
            'finalized_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(AttendanceEvent::class);
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    public function displayStatus(): string
    {
        if ($this->status === AttendanceStatus::Hadir || $this->status === AttendanceStatus::Terlambat) {
            if ($this->check_in_at && ! $this->check_out_at && ! $this->finalized_at) {
                return 'belum_pulang';
            }
        }

        return $this->status->value;
    }

    public function hasFlag(string $flag): bool
    {
        return in_array($flag, $this->flags ?? [], true);
    }
}
