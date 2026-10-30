# Hadir.In

Presensi kantor dan tim lapangan dengan geofence dan verifikasi selfie. Hadir.In
mencatat kehadiran harian, jadwal kerja, izin, cuti, dan laporan bulanan dalam satu
tempat. Antarmuka berbahasa Indonesia, tenang, dan dibuat untuk dipakai setiap hari.

## Status

Repositori ini sedang dibangun. Dokumentasi produk dan teknis sudah menjadi sumber
kebenaran. Implementasi aplikasi menyusul bertahap sesuai `docs/ROADMAP.md`.

| Bagian | Status |
| --- | --- |
| Dokumen produk, desain, arsitektur, database, rute, operasi | Selesai |
| Scaffold Laravel, Livewire, Tailwind | Belum |
| Autentikasi, undangan, tenancy, peran | Belum |
| Presensi, geofence, selfie, tinjauan, koreksi | Belum |
| Jadwal, hari libur, izin, cuti | Belum |
| Laporan bulanan dan ekspor CSV | Belum |
| Halaman publik dan aplikasi anggota | Belum |
| Pengujian otomatis dan runbook produksi | Belum |

Jangan menganggap fitur yang belum dicentang sebagai fitur yang sudah tersedia.

## Stack

- Laravel 12, PHP 8.3 atau lebih baru
- Livewire 3 dan Alpine.js
- Tailwind CSS 4 dan Vite
- PostgreSQL 16 di produksi, SQLite untuk pengujian lokal
- Pest untuk pengujian
- Penyimpanan selfie privat di disk lokal

Alasan pemilihan stack ada di `docs/DECISIONS.md`. Arsitektur ada di
`docs/ARCHITECTURE.md`. Spesifikasi visual ada di `DESIGN.md`.

## Menjalankan secara lokal

Prasyarat: PHP 8.3+ dengan ekstensi `pdo_sqlite`, `gd`, `mbstring`, `fileinfo`,
`zip`; Composer 2; Node 20+; npm.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm run dev
php artisan serve
```

Buka `http://127.0.0.1:8000`.

Untuk PostgreSQL, isi `DB_*` di `.env` dan pastikan ekstensi `pdo_pgsql` tersedia.
PHP lokal tanpa `pdo_pgsql` tetap bisa menjalankan aplikasi dan pengujian dengan
SQLite. Lihat `docs/OPERATIONS.md` untuk detail dan batasannya.

## Perintah penting

```bash
php artisan test                 # seluruh pengujian
php artisan test --filter=CheckInTest
./vendor/bin/pint                # format PHP
npm run build                    # build aset produksi
php artisan migrate:fresh --seed # reset database lokal
php artisan schedule:work        # jalankan penjadwal di lokal
php artisan queue:work           # jalankan worker antrean di lokal
```

## Struktur dokumentasi

- `DESIGN.md`: spesifikasi visual kanonik, token, komponen, dan pola layar
- `docs/PRD.md`: masalah, pengguna, ruang lingkup, aturan, kriteria penerimaan
- `docs/ARCHITECTURE.md`: batas modul, tenancy, alur data, antrean, penyimpanan
- `docs/DATABASE.md`: skema tabel, indeks, relasi, dan arti status
- `docs/ROUTES.md`: peta rute, nama rute, middleware, dan komponen
- `docs/TESTING.md`: strategi pengujian dan daftar kasus kritis
- `docs/OPERATIONS.md`: lingkungan, deployment, backup, pemantauan, pemecahan masalah
- `docs/ROADMAP.md`: urutan milestone dan kriteria selesai
- `docs/DECISIONS.md`: catatan keputusan arsitektur
- `docs/README.md`: peta dokumen dan urutan prioritas bila dokumen bertentangan

## Lisensi

Kode belum memiliki lisensi terbuka. Hak penggunaan belum ditetapkan.
