<?php

namespace App\Models;

use App\Enums\MembershipRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_platform_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_platform_admin' => 'boolean',
        ];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function activeMemberships(): HasMany
    {
        return $this->memberships()->where('is_active', true);
    }

    public function membershipFor(Tenant $tenant): ?Membership
    {
        return $this->memberships()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->first();
    }

    public function roleIn(Tenant $tenant): ?MembershipRole
    {
        return $this->membershipFor($tenant)?->role;
    }

    public function isMemberOf(Tenant $tenant): bool
    {
        return $this->membershipFor($tenant) !== null;
    }

    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = trim($value);
    }

    public function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = mb_strtolower(trim($value));
    }
}
