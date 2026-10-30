<?php

namespace App\Support;

class Flags
{
    public const TANPA_LOKASI = 'tanpa_lokasi';

    public const AKURASI_RENDAH = 'akurasi_rendah';

    public const DI_LUAR_RADIUS = 'di_luar_radius';

    public const DITERIMA_MANUAL = 'diterima_manual';

    public const FINAL_OTOMATIS = 'final_otomatis';

    public const TANPA_PULANG = 'tanpa_pulang';

    public const PULANG_TERLAMBAT = 'pulang_terlambat';

    public const PULANG_CEPAT = 'pulang_cepat';

    public const HARI_LIBUR = 'hari_libur';

    public const TANPA_JADWAL = 'tanpa_jadwal';

    public const PERLU_DITINJAU = 'perlu_ditinjau';

    public const DIKOREKSI = 'dikoreksi';

    public const LABELS = [
        self::TANPA_LOKASI => 'Tanpa lokasi',
        self::AKURASI_RENDAH => 'Akurasi rendah',
        self::DI_LUAR_RADIUS => 'Di luar radius',
        self::DITERIMA_MANUAL => 'Diterima manual',
        self::FINAL_OTOMATIS => 'Final otomatis',
        self::TANPA_PULANG => 'Tanpa pulang',
        self::PULANG_TERLAMBAT => 'Pulang terlambat',
        self::PULANG_CEPAT => 'Pulang cepat',
        self::HARI_LIBUR => 'Hari libur',
        self::TANPA_JADWAL => 'Tanpa jadwal',
        self::PERLU_DITINJAU => 'Perlu ditinjau',
        self::DIKOREKSI => 'Dikoreksi',
    ];

    public static function label(string $flag): string
    {
        return self::LABELS[$flag] ?? $flag;
    }

    public static function merge(?array $existing, array $added): array
    {
        return array_values(array_unique(array_merge($existing ?? [], $added)));
    }
}
