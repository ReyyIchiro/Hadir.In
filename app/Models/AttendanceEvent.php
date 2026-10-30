<?php

namespace App\Models;

use App\Enums\AttendanceEventStatus;
use App\Enums\AttendanceEventType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AttendanceEvent extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'attendance_day_id',
        'type',
        'status',
        'work_date',
        'occurred_at',
        'location_id',
        'latitude',
        'longitude',
        'accuracy_meters',
        'distance_meters',
        'selfie_path',
        'reason',
        'flags',
        'attempt_uuid',
    ];

    protected function casts(): array
    {
        return [
            'type' => AttendanceEventType::class,
            'status' => AttendanceEventStatus::class,
            'work_date' => 'date',
            'occurred_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'accuracy_meters' => 'decimal:2',
            'distance_meters' => 'integer',
            'flags' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceDay(): BelongsTo
    {
        return $this->belongsTo(AttendanceDay::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(AttendanceReview::class);
    }

    public function hasFlag(string $flag): bool
    {
        return in_array($flag, $this->flags ?? [], true);
    }
}
