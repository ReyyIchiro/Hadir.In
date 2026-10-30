<?php

namespace App\Models;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveRequest extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'reviewed_by_id',
        'review_note',
        'reviewed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => LeaveType::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => LeaveStatus::class,
            'reviewed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function attendanceDays(): HasMany
    {
        return $this->hasMany(AttendanceDay::class);
    }

    public function durationInDays(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }
}
