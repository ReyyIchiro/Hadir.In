# Hadir.In

Presensi kantor dan tim lapangan dengan geofence dan verifikasi selfie. Hadir.In
mencatat kehadiran harian, jadwal kerja, izin, cuti, dan laporan bulanan dalam satu
tempat. Antarmuka berbahasa Indonesia, tenang, dan dibuat untuk dipakai setiap hari.

## Status

Versi 1.0 lengkap untuk alur inti dan siap diuji di staging. Dokumentasi produk
dan teknis tetap menjadi sumber kebenaran bila kode dan dokumen berbeda.

| Bagian | Status |
| --- | --- |
| Dokumen produk, desain, arsitektur, database, rute, operasi | Selesai |
| Scaffold Laravel, Livewire, Tailwind | Selesai |
| Autentikasi, undangan, tenancy, peran | Selesai |
| Presensi, geofence, selfie, tinjauan, koreksi | Selesai |
| Jadwal, hari libur, izin, cuti | Selesai |
| Laporan bulanan dan ekspor CSV | Selesai |
| Halaman publik dan aplikasi anggota | Selesai |
| Pengujian otomatis dan runbook produksi | Selesai |

124 tes Pest lulus. Sebelum go-live, jalankan matriks manual kamera dan GPS pada
perangkat nyata (`docs/TESTING.md` bagian 7) dan uji staging PostgreSQL.

## Akun demo

Setelah `php artisan migrate:fresh --seed`, seeder demo membuat organisasi
"Hadir.In Demo" dengan kata sandi `password123` untuk semua akun:

| Peran | Email |
| --- | --- |
| Pemilik | `pemilik@hadir.test` |
| Admin | `admin@hadir.test` |
| Peninjau | `peninjau@hadir.test` |
| Anggota | `anggota@hadir.test` |
| Platform admin | `platform@hadir.test` |

Seeder demo hanya berjalan di lingkungan `local` dan `testing`, dan aman
dijalankan berulang.

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
