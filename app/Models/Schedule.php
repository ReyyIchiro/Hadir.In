<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'work_date',
        'is_day_off',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'is_day_off' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
