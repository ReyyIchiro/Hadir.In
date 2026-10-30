<?php

namespace App\Enums;

enum TenantStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu aktivasi',
            self::Active => 'Aktif',
            self::Suspended => 'Ditangguhkan',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}
