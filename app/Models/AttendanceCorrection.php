<?php

namespace App\Models;

use App\Enums\CorrectionStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrection extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'attendance_day_id',
        'user_id',
        'requested_by_id',
        'reviewed_by_id',
        'status',
        'original_values',
        'requested_values',
        'reason',
        'review_note',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CorrectionStatus::class,
            'original_values' => 'array',
            'requested_values' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function attendanceDay(): BelongsTo
    {
        return $this->belongsTo(AttendanceDay::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }
}
