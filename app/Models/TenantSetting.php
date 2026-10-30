<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSetting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'work_start',
        'work_end',
        'break_start',
        'break_end',
        'early_window_minutes',
        'grace_minutes',
        'checkout_late_minutes',
        'early_leave_minutes',
        'allow_unscheduled_attendance',
        'require_location',
        'require_selfie',
        'geofence_buffer_meters',
        'low_accuracy_meters',
        'gps_timeout_seconds',
        'selfie_retention_days',
    ];

    protected function casts(): array
    {
        return [
            'allow_unscheduled_attendance' => 'boolean',
            'require_location' => 'boolean',
            'require_selfie' => 'boolean',
            'early_window_minutes' => 'integer',
            'grace_minutes' => 'integer',
            'checkout_late_minutes' => 'integer',
            'early_leave_minutes' => 'integer',
            'geofence_buffer_meters' => 'integer',
            'low_accuracy_meters' => 'integer',
            'gps_timeout_seconds' => 'integer',
            'selfie_retention_days' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
