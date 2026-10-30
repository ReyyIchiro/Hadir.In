<?php

namespace App\Enums;

enum AttendanceEventType: string
{
    case CheckIn = 'check_in';
    case CheckOut = 'check_out';

    public function label(): string
    {
        return match ($this) {
            self::CheckIn => 'Absen masuk',
            self::CheckOut => 'Absen pulang',
        };
    }
}
