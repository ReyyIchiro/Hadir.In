<?php

namespace App\Models;

use App\Enums\MembershipRole;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    use BelongsToTenant, HasFactory, Prunable;

    protected $fillable = [
        'tenant_id',
        'email',
        'role',
        'primary_location_id',
        'division_id',
        'token_hash',
        'invited_by_id',
        'expires_at',
        'accepted_at',
        'accepted_by_id',
        'revoked_at',
    ];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return [
            'role' => MembershipRole::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_id');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_id');
    }

    public function primaryLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'primary_location_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Undangan disimpan 30 hari setelah kedaluwarsa sebagai jejak, lalu
     * dibersihkan oleh model:prune sesuai docs/DATABASE.md.
     */
    public function prunable(): Builder
    {
        return static::withoutGlobalScopes()
            ->where('expires_at', '<', now()->subDays(30));
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null
            && $this->revoked_at === null
            && $this->expires_at->isFuture();
    }

    public function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = mb_strtolower(trim($value));
    }
}
