# Peta Dokumentasi Hadir.In

Versi 1.0. Mulai dari sini bila Anda baru membuka repositori.

## 1. Urutan baca

Untuk memahami produk:

1. `../README.md`
2. `PRD.md`
3. `../DESIGN.md`

Untuk mulai menulis kode:

1. `ARCHITECTURE.md`
2. `DATABASE.md`
3. `ROUTES.md`
4. `../AGENTS.md`

Untuk menguji dan merilis:

1. `TESTING.md`
2. `OPERATIONS.md`
3. `ROADMAP.md`

Untuk memahami alasan:

1. `DECISIONS.md`

## 2. Daftar dokumen

| Berkas | Isi | Audiens |
| --- | --- | --- |
| `../README.md` | Ringkasan, status, cara menjalankan | Semua |
| `../AGENTS.md` | Aturan kerja agen dan kontributor | Kontributor |
| `../DESIGN.md` | Bahasa visual, token, komponen, pola layar | Desainer, pengembang UI |
| `PRD.md` | Masalah, peran, ruang lingkup, aturan, penerimaan | Produk, pengembang, QA |
| `ARCHITECTURE.md` | Stack, modul, tenancy, alur data, keamanan | Pengembang |
| `DATABASE.md` | Tabel, kolom, indeks, relasi, invariant | Pengembang, operator |
| `ROUTES.md` | URL, nama rute, middleware, komponen | Pengembang |
| `TESTING.md` | Strategi, kasus kritis, matriks manual, CI | QA, pengembang |
| `OPERATIONS.md` | Server, deploy, backup, pemantauan, insiden | Operator |
| `ROADMAP.md` | Milestone dan kriteria selesai | Semua |
| `DECISIONS.md` | ADR dan konsekuensinya | Pengembang, operator |

## 3. Prioritas bila bertentangan

1. `../DESIGN.md` untuk visual dan copy
2. `PRD.md` untuk perilaku produk
3. `DATABASE.md` untuk data
4. `ARCHITECTURE.md` untuk pola teknis
5. `ROUTES.md` untuk URL dan middleware
6. `DECISIONS.md` untuk alasan
7. `ROADMAP.md` untuk urutan

Bila dokumen bertentangan, perbaiki dokumen yang salah pada commit yang sama
dengan perubahan kode. Jangan membiarkan dua sumber kebenaran.

## 4. Istilah

| Istilah | Arti |
| --- | --- |
| Tenant | Organisasi yang memakai aplikasi |
| Owner | Pemilik organisasi |
| Admin | Pengelola organisasi |
| Peninjau | Reviewer yang menyetujui pengajuan |
| Anggota | Pengguna yang mencatat presensi |
| Platform admin | Pengelola instalasi, bukan anggota tenant |
| Presensi | Catatan kehadiran harian |
| Hari presensi | Satu baris per anggota per tanggal kerja |
| Kejadian presensi | Absen masuk atau pulang yang tercatat |
| Tinjauan | Permintaan persetujuan atas kejadian yang ditolak |
| Koreksi | Permintaan perubahan hari presensi |
| Izin | Ketidakhadiran terencana selain cuti |
| Cuti | Ketidakhadiran berbayar atau terencana |
| Sakit | Ketidakhadiran karena sakit |
| Hari libur | Tanggal nonkerja yang ditetapkan tenant |
| Jadwal | Rencana kerja per anggota per tanggal |
| Geofence | Batas radius lokasi kerja |
| Buffer | Toleransi jarak di luar radius |
| Finalisasi | Penetapan hari presensi oleh job |
| Work date | Tanggal kerja lokal tenant |

## 5. Konvensi penulisan

- Dokumen teknis memakai bahasa Indonesia dengan istilah teknis Inggris bila
  lebih tepat.
- Nama tabel, kolom, kelas, dan rute ditulis dalam `backtick`.
- Angka aturan memakai format `BR-xx`.
- Setiap aturan yang dapat diuji memiliki kriteria penerimaan di `PRD.md` atau
  `TESTING.md`.
- Jangan memakai emoji.
- Jangan memakai em dash. Gunakan tanda hubung biasa bila perlu.

## 6. Status dokumen

| Dokumen | Versi | Status |
| --- | --- | --- |
| `PRD.md` | 1.0 | Disetujui |
| `DESIGN.md` | 1.0 | Disetujui |
| `ARCHITECTURE.md` | 1.0 | Disetujui |
| `DATABASE.md` | 1.0 | Disetujui |
| `ROUTES.md` | 1.0 | Disetujui |
| `TESTING.md` | 1.0 | Disetujui |
| `OPERATIONS.md` | 1.0 | Disetujui |
| `ROADMAP.md` | 1.0 | Berjalan |
| `DECISIONS.md` | 1.0 | Berjalan |
