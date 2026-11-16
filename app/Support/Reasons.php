<?php

namespace App\Support;

/**
 * Human labels for the machine-readable reason codes stored on attendance
 * events. The database keeps the code; the interface never shows it raw.
 */
class Reasons
{
    public const LABELS = [
        'izin_disetujui' => 'Izin disetujui pada tanggal ini',
        'tidak_ada_jadwal' => 'Tidak ada jadwal kerja',
        'terlalu_awal' => 'Di luar jendela absen masuk',
        'jadwal_berakhir' => 'Jadwal kerja sudah berakhir',
        'sudah_masuk' => 'Sudah absen masuk',
        'sudah_pulang' => 'Sudah absen pulang',
        'belum_absen_masuk' => 'Belum absen masuk',
        'melewati_batas_pulang' => 'Melewati batas absen pulang',
        'di_luar_radius' => 'Di luar radius lokasi kerja',
        'akurasi_tidak_dapat_dipercaya' => 'Akurasi GPS tidak dapat dipercaya',
        'lokasi_tidak_tersedia' => 'Lokasi tidak tersedia',
        'selfie_wajib' => 'Selfie wajib diambil',
        'selfie_tidak_valid' => 'Selfie tidak dapat diproses',
        'duplikat' => 'Percobaan ganda',
        'tanpa_lokasi' => 'Tanpa lokasi',
        'akurasi_rendah' => 'Akurasi GPS rendah',
        'final_otomatis' => 'Ditutup otomatis',
    ];

    public static function label(?string $reason): string
    {
        if ($reason === null || $reason === '') {
            return 'Tidak ada';
        }

        return self::LABELS[$reason] ?? ucfirst(str_replace('_', ' ', $reason));
    }
}
