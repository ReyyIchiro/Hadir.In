<?php

namespace App\Models;

use App\Enums\MembershipRole;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'role',
        'primary_location_id',
        'division_id',
        'is_active',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => MembershipRole::class,
            'is_active' => 'boolean',
            'joined_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function primaryLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'primary_location_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
}
