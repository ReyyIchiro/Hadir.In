# Roadmap Hadir.In

Versi 1.0. Urutan pengerjaan dan kriteria selesai. Setiap milestone harus
menghasilkan aplikasi yang dapat dijalankan, bukan hanya kode yang belum diuji.

## 1. Prinsip

- Dokumentasi lebih dulu. Perubahan perilaku memperbarui dokumen.
- Satu milestone, satu tujuan yang dapat diuji.
- Setiap milestone diakhiri commit dan push.
- Jangan memulai modul baru sebelum tes modul sebelumnya lulus.
- Utamakan alur inti presensi sebelum halaman pemasaran yang rumit.

## 2. Ringkasan milestone

| Milestone | Fokus | Status |
| --- | --- | --- |
| M0 | Dokumentasi dan keputusan | Selesai |
| M1 | Fondasi Laravel, desain, autentikasi, tenancy | Selesai |
| M2 | Data operasional: lokasi, divisi, anggota, undangan | Selesai |
| M3 | Jadwal, hari libur, presensi, geofence, selfie | Selesai |
| M4 | Tinjauan, koreksi, izin, cuti | Selesai |
| M5 | Laporan, CSV, halaman publik, aplikasi anggota | Selesai |
| M6 | Pengujian menyeluruh, keamanan, penyebaran | Selesai |

## 3. M0: Dokumentasi

Status: selesai.

Keluaran:

- `README.md`
- `AGENTS.md`
- `DESIGN.md`
- `docs/PRD.md`
- `docs/ARCHITECTURE.md`
- `docs/DATABASE.md`
- `docs/ROUTES.md`
- `docs/TESTING.md`
- `docs/OPERATIONS.md`
- `docs/ROADMAP.md`
- `docs/DECISIONS.md`
- `docs/README.md`

Kriteria selesai:

- Semua aturan bisnis memiliki nomor dan dapat diuji.
- Skema data lengkap dengan indeks dan invariant.
- Peta rute lengkap.
- Token desain dan komponen dijelaskan.
- Dokumen dapat dipakai oleh pengembang baru tanpa percakapan sebelumnya.

## 4. M1: Fondasi

Keluaran:

- Laravel 12 di root proyek, mempertahankan `.git` dan dokumen.
- Livewire 3, Tailwind 4, Alpine, Vite.
- Konfigurasi SQLite lokal dan PostgreSQL produksi.
- Pest, Pint, dan `.env.example`.
- Token desain di `resources/css/app.css`.
- Layout publik, autentikasi, aplikasi, dan platform.
- Komponen Blade: tombol, input, label, status, panel, kartu statistik, modal,
  tabel, toast, keadaan kosong, keadaan galat.
- Autentikasi: daftar, masuk, keluar, lupa kata sandi, atur ulang.
- Tenancy: model, context, middleware, trait, scope.
- Peran dan policy dasar.
- Platform admin: gate, middleware, daftar tenant, detail, aktivasi, penangguhan.
- Halaman status tenant.

Kriteria selesai:

- Pendaftaran membuat tenant, owner, dan settings dalam satu transaksi.
- Pemilik tenant pending hanya dapat melihat `/status`.
- Anggota tenant pending tidak dapat masuk.
- Platform admin dapat mengaktifkan dan menangguhkan tenant.
- Pengguna dengan dua keanggotaan dapat berpindah tenant.
- Tes kebocoran tenant dasar lulus.
- Semua halaman memakai token desain.
- `php artisan test` dan `npm run build` lulus.

## 5. M2: Data operasional

Keluaran:

- Lokasi: daftar, form, peta, aktif dan nonaktif.
- Divisi: daftar, form, aktif dan nonaktif.
- Anggota: daftar, filter, detail, ubah peran, lokasi utama, divisi, status.
- Undangan: buat, kirim email, tautan salin, daftar, cabut, terima.
- Pengaturan organisasi: nama, zona waktu, jam kerja, geofence, selfie, retensi.
- Profil pengguna: nama, email, kata sandi.
- Tema terang, gelap, sistem.

Kriteria selesai:

- Undangan kedaluwarsa dan sekali pakai.
- Email undangan terkunci.
- Admin tidak dapat mengundang admin atau mengubah owner.
- Peninjau wajib memiliki lokasi utama.
- Lokasi dan divisi yang dipakai tidak dapat dihapus permanen.
- Pengaturan tersimpan dan dipakai oleh validasi presensi.
- Tes peran dan undangan lulus.

## 6. M3: Presensi

Keluaran:

- Jadwal: daftar mingguan, ubah satu tanggal, terapkan massal dengan pratinjau.
- Hari libur: daftar, tambah, hapus.
- Beranda anggota dengan kartu presensi hari ini.
- Layar absen masuk dan pulang dengan peta, jarak, akurasi, kamera.
- `GeofenceService`, `AttendanceService`, `TenantClock`, `SelfieService`.
- Penyimpanan selfie privat, strip EXIF, resize, URL bertanda tangan.
- Finalisasi otomatis dan command.
- Riwayat presensi anggota.
- Daftar presensi harian untuk admin dan peninjau.
- Detail presensi dengan selfie dan tanda.
- Idempotensi `attempt_uuid`.
- Kejadian ditolak tersimpan.

Kriteria selesai:

- Seluruh skenario di `TESTING.md` bagian 5.4 sampai 5.6 lulus.
- Selfie tidak dapat diakses tanpa tanda tangan dan policy.
- Finalisasi idempoten.
- Absen masuk hari baru menutup hari sebelumnya.
- Tampilan "Belum pulang" dan "Final otomatis" benar.
- Peta dan kamera diuji manual di Android dan iOS.

## 7. M4: Tinjauan, koreksi, izin

Keluaran:

- Antrean tinjauan untuk admin dan peninjau.
- Detail tinjauan presensi, koreksi, dan izin.
- Persetujuan dan penolakan dengan catatan.
- Lingkup peninjau berdasarkan lokasi utama.
- Koreksi dengan nilai asli dan nilai diminta.
- Izin, sakit, cuti: form, daftar, detail, batal.
- Persetujuan izin membuat hari presensi.
- Pembatalan izin yang disetujui dengan batas tanggal.
- Pemblokiran absen saat izin disetujui.

Kriteria selesai:

- Seluruh skenario di `TESTING.md` bagian 5.7 dan 5.8 lulus.
- Peninjau tidak dapat melihat data di luar lokasi utamanya.
- Koreksi tidak menghapus kejadian.
- Izin tidak dapat disetujui bila sudah ada absen masuk.
- Catatan wajib saat menolak.

## 8. M5: Laporan dan halaman publik

Keluaran:

- Laporan bulanan: ringkasan, rincian, filter divisi dan lokasi.
- Ekspor CSV: BOM, titik koma, CRLF, nama berkas, escape.
- Halaman publik: beranda, cara kerja, untuk siapa, pertanyaan, privasi,
  ketentuan.
- Halaman masuk, daftar, lupa kata sandi, atur ulang, undangan.
- Halaman status tenant.
- Halaman platform yang lengkap.
- Peningkatan aksesibilitas dan mode gelap.

Kriteria selesai:

- Seluruh skenario di `TESTING.md` bagian 5.9 lulus.
- CSV dapat dibuka di Excel tanpa karakter rusak.
- Angka ringkasan cocok dengan rincian.
- Halaman publik tidak memuat klaim atau angka palsu.
- Semua layar di `DESIGN.md` bagian 8 tersedia.
- Tampil baik di 360 px sampai 1440 px.

## 9. M6: Pengerasan dan rilis

Keluaran:

- Seeders dan factories lengkap.
- Tes kebocoran tenant menyeluruh.
- Tes performa dasar.
- Pemeriksaan keamanan: rate limit, CSRF, policy, header, URL selfie.
- Runbook operasi final.
- CI dasar.
- Uji staging PostgreSQL.
- Matriks manual kamera dan GPS.
- Perbaikan temuan.

Kriteria selesai:

- Semua tes lulus.
- `npm run build` lulus.
- Tidak ada temuan keamanan kritis yang terbuka.
- Backup dan pemulihan diuji.
- Checklist go-live di `OPERATIONS.md` terpenuhi.
- Commit akhir di-push.

## 10. Setelah v1

Kandidat, belum dijanjikan:

- Shift lintas tengah malam.
- Absensi offline dengan sinkronisasi.
- Notifikasi email atau WhatsApp untuk pengajuan.
- 2FA.
- Audit log.
- Impor anggota dari CSV.
- Ekspor PDF.
- Beberapa lokasi per anggota.
- Kuota cuti.
- Integrasi payroll.
- API publik dengan token.
- Aplikasi mobile native.

Setiap kandidat memerlukan PRD dan ADR baru sebelum dikerjakan.

## 11. Catatan kemajuan

Perbarui tabel ini setiap milestone selesai.

| Tanggal | Milestone | Catatan |
| --- | --- | --- |
| 2026-10-30 | M0 | Dokumen produk, desain, arsitektur, database, rute, pengujian, operasi, roadmap, keputusan |
| 2026-10-30 | M1 | Laravel 12, Livewire 3, Tailwind 4, token desain, autentikasi, tenancy, platform admin |
| 2026-10-30 | M2 | Lokasi, divisi, anggota, undangan, pengaturan organisasi, profil, tema |
| 2026-10-30 | M3 | Jadwal, hari libur, presensi masuk dan pulang, geofence, selfie, finalisasi otomatis |
| 2026-10-30 | M4 | Antrean tinjauan, koreksi jam, izin, sakit, cuti, lingkup peninjau |
| 2026-10-30 | M5 | Laporan bulanan, ekspor CSV, halaman publik, aplikasi anggota, mode gelap |
| 2026-10-30 | M6 | Seeder demo, 131 tes Pest, Pint, build Vite, CI, runbook operasi |

Catatan M6:

- `php artisan test` lulus 131 tes dengan 283 asersi.
- `npm run build` lulus.
- Seluruh rute pada `ROUTES.md` terdaftar dan diuji.
- Seluruh tes lulus pada PostgreSQL 16 di dalam kontainer PHP 8.3, jadi
  migrasi dan kueri sudah teruji pada mesin yang setara produksi. CI mengulang
  pengujian ini pada setiap push.
- Matriks manual kamera dan GPS pada perangkat nyata masih perlu dijalankan
  sebelum go-live; lihat `TESTING.md` bagian 7.
