<?php

namespace App\Support;

use App\Models\Tenant;
use Carbon\CarbonImmutable;

class Waktu
{
    public const MONTHS = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public const MONTHS_SHORT = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Agu',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des',
    ];

    public const DAYS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    public static function now(Tenant $tenant): CarbonImmutable
    {
        return CarbonImmutable::now($tenant->timezone);
    }

    public static function today(Tenant $tenant): CarbonImmutable
    {
        return self::now($tenant)->startOfDay();
    }

    public static function local(CarbonImmutable|\Carbon\CarbonInterface $value, Tenant $tenant): CarbonImmutable
    {
        return CarbonImmutable::instance($value)->setTimezone($tenant->timezone);
    }

    public static function utc(CarbonImmutable|\Carbon\CarbonInterface $value, Tenant $tenant): CarbonImmutable
    {
        return CarbonImmutable::instance($value)->setTimezone('UTC');
    }

    public static function dateLong(\DateTimeInterface|string $value): string
    {
        $date = $value instanceof \DateTimeInterface
            ? CarbonImmutable::instance($value)
            : CarbonImmutable::parse($value);

        return $date->day.' '.self::MONTHS[$date->month].' '.$date->year;
    }

    public static function dateShort(\DateTimeInterface|string $value): string
    {
        $date = $value instanceof \DateTimeInterface
            ? CarbonImmutable::instance($value)
            : CarbonImmutable::parse($value);

        return $date->day.' '.self::MONTHS_SHORT[$date->month].' '.$date->year;
    }

    public static function dayName(\DateTimeInterface|string $value): string
    {
        $date = $value instanceof \DateTimeInterface
            ? CarbonImmutable::instance($value)
            : CarbonImmutable::parse($value);

        return self::DAYS[$date->dayOfWeekIso];
    }

    public static function time(\DateTimeInterface|string|null $value, string $fallback = '--:--'): string
    {
        if ($value === null) {
            return $fallback;
        }

        $date = $value instanceof \DateTimeInterface
            ? CarbonImmutable::instance($value)
            : CarbonImmutable::parse($value);

        return $date->format('H.i');
    }

    public static function monthLabel(int $month): string
    {
        return self::MONTHS[$month] ?? '';
    }

    public static function relativeTime(\DateTimeInterface $value): string
    {
        return CarbonImmutable::instance($value)->diffForHumans(now(), [
            'locale' => 'id',
            'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_AUTO,
        ]);
    }
}
