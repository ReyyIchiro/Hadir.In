<?php

namespace App\Enums;

enum MembershipRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Reviewer = 'reviewer';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Pemilik',
            self::Admin => 'Admin',
            self::Reviewer => 'Peninjau',
            self::Member => 'Anggota',
        };
    }

    public function canManageMembers(): bool
    {
        return in_array($this, [self::Owner, self::Admin], true);
    }

    public function canManageSettings(): bool
    {
        return in_array($this, [self::Owner, self::Admin], true);
    }

    public function canReview(): bool
    {
        return in_array($this, [self::Owner, self::Admin, self::Reviewer], true);
    }

    public function canManageSchedules(): bool
    {
        return in_array($this, [self::Owner, self::Admin], true);
    }

    public function isOwner(): bool
    {
        return $this === self::Owner;
    }

    public function rank(): int
    {
        return match ($this) {
            self::Owner => 4,
            self::Admin => 3,
            self::Reviewer => 2,
            self::Member => 1,
        };
    }
}
