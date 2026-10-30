<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Hadir = 'hadir';
    case Terlambat = 'terlambat';
    case Izin = 'izin';
    case Sakit = 'sakit';
    case Cuti = 'cuti';
    case Absen = 'absen';

    public function label(): string
    {
        return match ($this) {
            self::Hadir => 'Hadir',
            self::Terlambat => 'Terlambat',
            self::Izin => 'Izin',
            self::Sakit => 'Sakit',
            self::Cuti => 'Cuti',
            self::Absen => 'Absen',
        };
    }

    public function isPresent(): bool
    {
        return in_array($this, [self::Hadir, self::Terlambat], true);
    }
}
