# Database Hadir.In

Versi 1.0. PostgreSQL 16 di produksi. SQLite di memori untuk pengujian lokal.
Semua migrasi harus portabel untuk keduanya. Dokumen ini adalah sumber kebenaran
untuk nama tabel, kolom, indeks, dan arti nilai.

## 1. Prinsip

- Nama tabel jamak, huruf kecil, pemisah garis bawah.
- Kunci utama `bigint unsigned` auto increment, kecuali tabel bawaan Laravel.
- Waktu disimpan UTC pada kolom `timestamp`. Tanggal kerja disimpan pada kolom
  `date` terpisah.
- Status disimpan sebagai string, bukan enum database. Nilai divalidasi oleh enum
  PHP dan dijelaskan di dokumen ini.
- Tidak ada penghapusan data presensi. Penghapusan hanya untuk data sementara
  seperti sesi, undangan kedaluwarsa, dan hari izin yang dibatalkan sebelum ada
  kejadian.
- Semua tabel operasional memiliki `tenant_id` dan indeks yang dimulai dengan
  `tenant_id`.
- Tidak memakai `enum`, `jsonb`, `timestamptz`, generated column, atau partial
  index agar SQLite dan PostgreSQL sama-sama berjalan.
- Nama constraint dan indeks eksplisit bila panjang atau maknanya penting.

## 2. Daftar tabel

| Tabel | Fungsi |
| --- | --- |
| `users` | Akun pengguna lintas organisasi |
| `tenants` | Organisasi |
| `tenant_settings` | Aturan presensi per organisasi |
| `memberships` | Peran pengguna dalam organisasi |
| `invitations` | Undangan bergabung |
| `locations` | Titik kerja dengan geofence |
| `divisions` | Kelompok kerja |
| `schedules` | Jadwal per anggota per tanggal |
| `holidays` | Hari libur organisasi |
| `attendance_days` | Satu baris kehadiran per anggota per tanggal |
| `attendance_events` | Semua percobaan absen, termasuk yang ditolak |
| `attendance_reviews` | Tinjauan percobaan ditolak |
| `attendance_corrections` | Koreksi data hari presensi |
| `leave_requests` | Izin, sakit, dan cuti |
| `sessions` | Sesi pengguna |
| `cache`, `cache_locks` | Cache bawaan Laravel |
| `jobs`, `job_batches`, `failed_jobs` | Antrean bawaan Laravel |
| `password_reset_tokens` | Token atur ulang kata sandi |

## 3. Tabel users

Akun global. Satu pengguna dapat menjadi anggota beberapa organisasi.

| Kolom | Tipe | Null | Default | Keterangan |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | tidak | auto | Kunci utama |
| `name` | varchar(120) | tidak | | Nama tampilan |
| `email` | varchar(190) | tidak | | Unik, disimpan huruf kecil |
| `password` | varchar(255) | tidak | | Hash bcrypt |
| `is_platform_admin` | boolean | tidak | false | Penanda admin platform |
| `remember_token` | varchar(100) | ya | null | Bawaan Laravel |
| `created_at` | timestamp | ya | null | |
| `updated_at` | timestamp | ya | null | |

Indeks:

- `users_email_unique` unik pada `email`.
- `users_is_platform_admin_index` pada `is_platform_admin`.

Aturan:

- Email dinormalisasi ke huruf kecil sebelum simpan dan sebelum login.
- Platform admin tidak boleh memiliki baris `memberships`.
- Tidak ada kolom verifikasi email di v1.

## 4. Tabel tenants

| Kolom | Tipe | Null | Default | Keterangan |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | tidak | auto | Kunci utama |
| `name` | varchar(120) | tidak | | Nama organisasi |
| `slug` | varchar(140) | tidak | | Unik, huruf kecil, tanda hubung |
| `timezone` | varchar(64) | tidak | `Asia/Jakarta` | Zona IANA |
| `status` | varchar(20) | tidak | `pending` | `pending`, `active`, `suspended` |
| `plan` | varchar(30) | tidak | `standard` | Label paket, tidak dipakai untuk penagihan |
| `trial_ends_at` | timestamp | ya | null | Catatan masa uji, tidak memblokir akses |
| `created_at` | timestamp | ya | null | |
| `updated_at` | timestamp | ya | null | |

Indeks:

- `tenants_slug_unique` unik pada `slug`.
- `tenants_status_index` pada `status`.

Status:

- `pending`: baru dibuat, menunggu aktivasi platform admin. Pemilik dapat masuk
  tetapi hanya melihat halaman status. Anggota tidak dapat masuk.
- `active`: operasional penuh.
- `suspended`: akses anggota diblokir. Pemilik melihat halaman status.

Tidak ada penghapusan tenant dari aplikasi. Penghapusan hanya lewat operasi
database manual dengan prosedur di `OPERATIONS.md`.

## 5. Tabel tenant_settings

Satu baris per tenant. Dibuat otomatis saat tenant dibuat.

| Kolom | Tipe | Null | Default | Keterangan |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | tidak | auto | Kunci utama |
| `tenant_id` | bigint unsigned | tidak | | FK tenants, cascade |
| `work_start` | time | tidak | `08:00:00` | Jam mulai bawaan |
| `work_end` | time | tidak | `17:00:00` | Jam selesai bawaan |
| `break_start` | time | ya | null | Awal istirahat |
| `break_end` | time | ya | null | Akhir istirahat |
| `early_window_minutes` | smallint unsigned | tidak | 60 | Jendela absen masuk awal |
| `grace_minutes` | smallint unsigned | tidak | 10 | Toleransi terlambat |
| `checkout_late_minutes` | smallint unsigned | tidak | 240 | Batas pulang terlambat |
| `early_leave_minutes` | smallint unsigned | tidak | 10 | Ambang pulang cepat |
| `allow_unscheduled_attendance` | boolean | tidak | false | Izinkan absen tanpa jadwal |
| `require_location` | boolean | tidak | true | Wajib GPS |
| `require_selfie` | boolean | tidak | true | Wajib selfie |
| `geofence_buffer_meters` | smallint unsigned | tidak | 50 | Buffer radius |
| `low_accuracy_meters` | smallint unsigned | tidak | 100 | Ambang akurasi rendah |
| `gps_timeout_seconds` | smallint unsigned | tidak | 15 | Waktu tunggu GPS |
| `selfie_retention_days` | smallint unsigned | tidak | 90 | Retensi selfie |
| `created_at` | timestamp | ya | null | |
| `updated_at` | timestamp | ya | null | |

Indeks:

- `tenant_settings_tenant_id_unique` unik pada `tenant_id`.

Batasan:

- `work_end` harus lebih besar dari `work_start`.
- Bila `break_start` diisi, `break_end` wajib diisi, dan sebaliknya.
- `break_start` dan `break_end` harus di antara `work_start` dan `work_end`.
- `grace_minutes`, `early_window_minutes`, dan `checkout_late_minutes` bernilai
  antara 0 dan 1440.
- `selfie_retention_days` antara 7 dan 365.

## 6. Tabel memberships

| Kolom | Tipe | Null | Default | Keterangan |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | tidak | auto | Kunci utama |
| `tenant_id` | bigint unsigned | tidak | | FK tenants, cascade |
| `user_id` | bigint unsigned | tidak | | FK users, cascade |
| `role` | varchar(20) | tidak | `member` | `owner`, `admin`, `reviewer`, `member` |
| `primary_location_id` | bigint unsigned | ya | null | FK locations, null on delete |
| `division_id` | bigint unsigned | ya | null | FK divisions, null on delete |
| `is_active` | boolean | tidak | true | Keanggotaan aktif |
| `joined_at` | timestamp | ya | null | Waktu bergabung |
| `created_at` | timestamp | ya | null | |
| `updated_at` | timestamp | ya | null | |

Indeks:

- `memberships_tenant_user_unique` unik pada `(tenant_id, user_id)`.
- `memberships_tenant_role_index` pada `(tenant_id, role)`.
- `memberships_tenant_primary_location_index` pada `(tenant_id, primary_location_id)`.
- `memberships_tenant_division_index` pada `(tenant_id, division_id)`.

Aturan:

- Setiap tenant wajib memiliki tepat satu `owner` aktif. Tidak ada constraint
  database untuk ini; dijaga service dan pengujian.
- `reviewer` wajib memiliki `primary_location_id`.
- `owner` dan `admin` tidak wajib memiliki lokasi utama.
- Peran tidak dapat diubah menjadi `owner` lewat antarmuka. Hanya pendaftaran
  yang membuat owner.
- Penghapusan anggota adalah menonaktifkan `is_active`, bukan menghapus baris,
  agar riwayat presensi tetap terhubung.

## 7. Tabel invitations

| Kolom | Tipe | Null | Default | Keterangan |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | tidak | auto | Kunci utama |
| `tenant_id` | bigint unsigned | tidak | | FK tenants, cascade |
| `email` | varchar(190) | tidak | | Huruf kecil |
| `role` | varchar(20) | tidak | `member` | Peran yang diundang |
| `primary_location_id` | bigint unsigned | ya | null | FK locations, null on delete |
| `division_id` | bigint unsigned | ya | null | FK divisions, null on delete |
| `token_hash` | char(64) | tidak | | SHA-256 dari token |
| `invited_by_id` | bigint unsigned | ya | null | FK users, null on delete |
| `expires_at` | timestamp | tidak | | 7 hari setelah dibuat |
| `accepted_at` | timestamp | ya | null | Waktu diterima |
| `accepted_by_id` | bigint unsigned | ya | null | FK users, null on delete |
| `revoked_at` | timestamp | ya | null | Waktu dicabut |
| `created_at` | timestamp | ya | null | |
| `updated_at` | timestamp | ya | null | |

Indeks:

- `invitations_token_hash_unique` unik pada `token_hash`.
- `invitations_tenant_email_index` pada `(tenant_id, email)`.
- `invitations_tenant_expires_index` pada `(tenant_id, expires_at)`.

Aturan:

- Token asli hanya ada di tautan dan email. Database hanya menyimpan hash.
- Undangan dianggap aktif bila `accepted_at`, `revoked_at`, dan `expires_at`
  memenuhi syarat. Pengecekan dilakukan di service.
- Satu email hanya boleh memiliki satu undangan aktif per tenant. Tidak memakai
  unique index karena undangan lama tetap disimpan.

## 8. Tabel locations

| Kolom | Tipe | Null | Default | Keterangan |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | tidak | auto | Kunci utama |
| `tenant_id` | bigint unsigned | tidak | | FK tenants, cascade |
| `name` | varchar(120) | tidak | | Nama lokasi |
| `address` | varchar(255) | ya | null | Alamat tampilan |
| `latitude` | decimal(10,7) | tidak | | -90 sampai 90 |
| `longitude` | decimal(10,7) | tidak | | -180 sampai 180 |
| `radius_meters` | smallint unsigned | tidak | 100 | 25 sampai 5000 |
| `is_active` | boolean | tidak | true | Lokasi aktif |
| `created_at` | timestamp | ya | null | |
| `updated_at` | timestamp | ya | null | |

Indeks:

- `locations_tenant_active_index` pada `(tenant_id, is_active)`.
- `locations_tenant_name_index` pada `(tenant_id, name)`.

Aturan:

- Lokasi tidak dihapus permanen bila sudah dipakai presensi. Nonaktifkan.
- Radius minimum 25 meter untuk menghindari geofence yang tidak berarti.
- Lokasi nonaktif tidak dipakai untuk validasi baru, tetapi tetap tampil pada
  riwayat.

## 9. Tabel divisions

| Kolom | Tipe | Null | Default | Keterangan |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | tidak | auto | Kunci utama |
| `tenant_id` | bigint unsigned | tidak | | FK tenants, cascade |
| `name` | varchar(80) | tidak | | Nama divisi |
| `description` | varchar(255) | ya | null | Keterangan |
| `is_active` | boolean | tidak | true | Divisi aktif |
| `created_at` | timestamp | ya | null | |
| `updated_at` | timestamp | ya | null | |

Indeks:

- `divisions_tenant_name_unique` unik pada `(tenant_id, name)`.
- `divisions_tenant_active_index` pada `(tenant_id, is_active)`.

## 10. Tabel schedules

| Kolom | Tipe | Null | Default | Keterangan |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | tidak | auto | Kunci utama |
| `tenant_id` | bigint unsigned | tidak | | FK tenants, cascade |
| `user_id` | bigint unsigned | tidak | | FK users, cascade |
| `work_date` | date | tidak | | Tanggal lokal |
| `is_day_off` | boolean | tidak | false | Hari libur jadwal |
| `start_time` | time | ya | null | Jam lokal |
| `end_time` | time | ya | null | Jam lokal |
| `break_start` | time | ya | null | Jam lokal |
| `break_end` | time | ya | null | Jam lokal |
| `note` | varchar(255) | ya | null | Catatan |
| `created_at` | timestamp | ya | null | |
| `updated_at` | timestamp | ya | null | |

Indeks:

- `schedules_tenant_user_date_unique` unik pada `(tenant_id, user_id, work_date)`.
- `schedules_tenant_date_index` pada `(tenant_id, work_date)`.
- `schedules_tenant_user_index` pada `(tenant_id, user_id)`.

Batasan:

- Bila `is_day_off = false`, `start_time` dan `end_time` wajib diisi dan
  `end_time > start_time`.
- Bila `is_day_off = true`, semua jam boleh null.
- Jam istirahat mengikuti aturan `tenant_settings`.
- Tidak ada shift lintas tengah malam.

## 11. Tabel holidays

| Kolom | Tipe | Null | Default | Keterangan |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | tidak | auto | Kunci utama |
| `tenant_id` | bigint unsigned | tidak | | FK tenants, cascade |
| `date` | date | tidak | | Tanggal lokal |
| `name` | varchar(120) | tidak | | Nama hari libur |
| `created_at` | timestamp | ya | null | |
| `updated_at` | timestamp | ya | null | |

Indeks:

- `holidays_tenant_date_unique` unik pada `(tenant_id, date)`.
- `holidays_tenant_date_index` pada `(tenant_id, date)`.

## 12. Tabel attendance_days

Satu baris per anggota per tanggal yang memiliki kejadian atau status. Hari tanpa
jadwal, tanpa kejadian, dan tanpa izin tidak dibuat.

| Kolom | Tipe | Null | Default | Keterangan |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | tidak | auto | Kunci utama |
| `tenant_id` | bigint unsigned | tidak | | FK tenants, cascade |
| `user_id` | bigint unsigned | tidak | | FK users, cascade |
| `work_date` | date | tidak | | Tanggal lokal |
| `schedule_id` | bigint unsigned | ya | null | FK schedules, null on delete |
| `location_id` | bigint unsigned | ya | null | Lokasi absen masuk, FK locations |
| `leave_request_id` | bigint unsigned | ya | null | FK leave_requests, null on delete |
| `status` | varchar(20) | tidak | | `hadir`, `terlambat`, `izin`, `sakit`, `cuti`, `absen` |
| `check_in_at` | timestamp | ya | null | UTC |
| `check_out_at` | timestamp | ya | null | UTC |
| `late_minutes` | smallint unsigned | tidak | 0 | Menit keterlambatan |
| `early_leave_minutes` | smallint unsigned | tidak | 0 | Menit pulang cepat |
| `flags` | json | ya | null | Array string tanda |
| `note` | text | ya | null | Catatan internal |
| `is_corrected` | boolean | tidak | false | Pernah dikoreksi |
| `finalized_at` | timestamp | ya | null | Waktu finalisasi |
| `created_at` | timestamp | ya | null | |
| `updated_at` | timestamp | ya | null | |

Indeks:

- `attendance_days_tenant_user_date_unique` unik pada `(tenant_id, user_id, work_date)`.
- `attendance_days_tenant_date_status_index` pada `(tenant_id, work_date, status)`.
- `attendance_days_tenant_location_index` pada `(tenant_id, location_id)`.
- `attendance_days_tenant_user_index` pada `(tenant_id, user_id)`.
- `attendance_days_leave_request_index` pada `leave_request_id`.

Aturan:

- `check_out_at` hanya boleh diisi bila `check_in_at` terisi.
- `check_out_at` harus lebih besar dari `check_in_at`.
- `late_minutes` dan `early_leave_minutes` tidak negatif.
- Status `izin`, `sakit`, `cuti` tidak memiliki `check_in_at` atau `check_out_at`.
- Status `absen` tidak memiliki `check_in_at` atau `check_out_at`.
- Status `hadir` dan `terlambat` wajib memiliki `check_in_at`.
- `flags` selalu array, default `[]` saat dibaca.
- Hari yang sudah `finalized_at` hanya berubah lewat koreksi yang disetujui.

Status yang disimpan:

| Nilai | Arti | Sumber |
| --- | --- | --- |
| `hadir` | Masuk tepat waktu atau dalam toleransi | Presensi |
| `terlambat` | Masuk setelah toleransi | Presensi |
| `izin` | Izin disetujui | Izin |
| `sakit` | Sakit disetujui | Izin |
| `cuti` | Cuti disetujui | Izin |
| `absen` | Tidak ada presensi pada hari terjadwal | Finalisasi |

Status tampilan turunan:

| Tampilan | Syarat |
| --- | --- |
| `belum_pulang` | `check_in_at` ada, `check_out_at` null, `finalized_at` null |
| `belum_absen` | Ada jadwal kerja, belum ada hari presensi, dan `now` belum melewati `end_time` jadwal + `checkout_late_minutes` |
| `libur` | Tidak ada jadwal kerja atau jadwal `is_day_off` atau hari libur |
| `perlu_ditinjau` | Ada tinjauan `pending` untuk kejadian pada hari itu |

## 13. Tabel attendance_events

Menyimpan setiap percobaan absen. Kejadian ditolak tidak memiliki hari presensi.

| Kolom | Tipe | Null | Default | Keterangan |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | tidak | auto | Kunci utama |
| `tenant_id` | bigint unsigned | tidak | | FK tenants, cascade |
| `user_id` | bigint unsigned | tidak | | FK users, cascade |
| `attendance_day_id` | bigint unsigned | ya | null | FK attendance_days, null on delete |
| `type` | varchar(20) | tidak | | `check_in`, `check_out` |
| `status` | varchar(20) | tidak | | `accepted`, `rejected` |
| `work_date` | date | tidak | | Tanggal lokal |
| `occurred_at` | timestamp | tidak | | UTC |
| `location_id` | bigint unsigned | ya | null | FK locations, null on delete |
| `latitude` | decimal(10,7) | ya | null | Koordinat dikirim |
| `longitude` | decimal(10,7) | ya | null | Koordinat dikirim |
| `accuracy_meters` | decimal(8,2) | ya | null | Akurasi GPS |
| `distance_meters` | integer unsigned | ya | null | Jarak ke lokasi |
| `selfie_path` | varchar(255) | ya | null | Path disk privat |
| `reason` | varchar(80) | ya | null | Kode alasan penolakan |
| `flags` | json | ya | null | Array string tanda |
| `attempt_uuid` | char(36) | tidak | | Idempotensi |
| `created_at` | timestamp | ya | null | |
| `updated_at` | timestamp | ya | null | |

Indeks:

- `attendance_events_attempt_uuid_unique` unik pada `attempt_uuid`.
- `attendance_events_tenant_user_date_index` pada `(tenant_id, user_id, work_date)`.
- `attendance_events_tenant_occurred_index` pada `(tenant_id, occurred_at)`.
- `attendance_events_tenant_location_index` pada `(tenant_id, location_id)`.
- `attendance_events_day_index` pada `attendance_day_id`.

Kode alasan penolakan:

| Kode | Arti |
| --- | --- |
| `terlalu_awal` | Sebelum jendela absen masuk |
| `jadwal_berakhir` | Setelah jam selesai |
| `di_luar_radius` | Jarak melebihi radius dan buffer |
| `lokasi_tidak_tersedia` | GPS wajib tetapi tidak tersedia |
| `selfie_wajib` | Selfie wajib tetapi tidak ada |
| `selfie_tidak_valid` | Berkas bukan gambar yang sah |
| `sudah_masuk` | Sudah ada absen masuk |
| `sudah_pulang` | Sudah ada absen pulang |
| `belum_absen_masuk` | Absen pulang tanpa absen masuk |
| `melewati_batas_pulang` | Lebih dari 24 jam sejak masuk |
| `izin_disetujui` | Ada izin disetujui |
| `tidak_ada_jadwal` | Tidak ada jadwal dan tidak diizinkan |
| `hari_libur_jadwal` | Jadwal menandai hari libur dan tidak diizinkan |
| `duplikat` | Percobaan duplikat dari UUID yang sama |

## 14. Tabel attendance_reviews

| Kolom | Tipe | Null | Default | Keterangan |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | tidak | auto | Kunci utama |
| `tenant_id` | bigint unsigned | tidak | | FK tenants, cascade |
| `attendance_event_id` | bigint unsigned | tidak | | FK attendance_events, cascade |
| `user_id` | bigint unsigned | tidak | | Anggota pemilik kejadian |
| `reviewer_id` | bigint unsigned | ya | null | FK users, null on delete |
| `status` | varchar(20) | tidak | `pending` | `pending`, `approved`, `rejected` |
| `note` | text | ya | null | Catatan pengaju |
| `review_note` | text | ya | null | Catatan peninjau |
| `reviewed_at` | timestamp | ya | null | UTC |
| `created_at` | timestamp | ya | null | |
| `updated_at` | timestamp | ya | null | |

Indeks:

- `attendance_reviews_tenant_status_index` pada `(tenant_id, status)`.
- `attendance_reviews_event_index` pada `attendance_event_id`.
- `attendance_reviews_tenant_user_index` pada `(tenant_id, user_id)`.

Aturan:

- Hanya kejadian dengan `status = rejected` yang boleh ditinjau.
- Satu kejadian hanya boleh memiliki satu tinjauan `pending`.
- Penolakan wajib memiliki `review_note`.

## 15. Tabel attendance_corrections

| Kolom | Tipe | Null | Default | Keterangan |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | tidak | auto | Kunci utama |
| `tenant_id` | bigint unsigned | tidak | | FK tenants, cascade |
| `attendance_day_id` | bigint unsigned | tidak | | FK attendance_days, cascade |
| `user_id` | bigint unsigned | tidak | | Anggota pemilik hari |
| `requested_by_id` | bigint unsigned | tidak | | FK users, cascade |
| `reviewed_by_id` | bigint unsigned | ya | null | FK users, null on delete |
| `status` | varchar(20) | tidak | `pending` | `pending`, `approved`, `rejected` |
| `original_values` | json | tidak | | Nilai sebelum koreksi |
| `requested_values` | json | tidak | | Nilai yang diminta |
| `reason` | text | tidak | | Alasan koreksi |
| `review_note` | text | ya | null | Catatan peninjau |
| `reviewed_at` | timestamp | ya | null | UTC |
| `created_at` | timestamp | ya | null | |
| `updated_at` | timestamp | ya | null | |

Indeks:

- `attendance_corrections_tenant_status_index` pada `(tenant_id, status)`.
- `attendance_corrections_day_index` pada `attendance_day_id`.
- `attendance_corrections_tenant_user_index` pada `(tenant_id, user_id)`.

Bentuk `original_values` dan `requested_values`:

```json
{
  "check_in_at": "2026-10-30T01:02:00Z",
  "check_out_at": "2026-10-30T10:05:00Z",
  "status": "hadir",
  "note": "Lupa absen pulang"
}
```

Nilai waktu memakai ISO 8601 UTC. Hanya kunci yang berubah yang perlu ada di
`requested_values`. `original_values` selalu diisi lengkap saat pengajuan.

Aturan:

- Satu hari hanya boleh memiliki satu koreksi `pending`.
- Koreksi hanya untuk hari dalam 7 hari terakhir, dihitung dari tanggal kerja
  lokal.
- Koreksi yang disetujui mengubah `attendance_days` dan menandai
  `is_corrected = true`.
- Kejadian presensi tidak dihapus.

## 16. Tabel leave_requests

| Kolom | Tipe | Null | Default | Keterangan |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | tidak | auto | Kunci utama |
| `tenant_id` | bigint unsigned | tidak | | FK tenants, cascade |
| `user_id` | bigint unsigned | tidak | | FK users, cascade |
| `type` | varchar(20) | tidak | | `izin`, `sakit`, `cuti` |
| `start_date` | date | tidak | | Tanggal lokal |
| `end_date` | date | tidak | | Tanggal lokal |
| `reason` | text | tidak | | Alasan pengajuan |
| `status` | varchar(20) | tidak | `pending` | `pending`, `approved`, `rejected`, `cancelled` |
| `reviewed_by_id` | bigint unsigned | ya | null | FK users, null on delete |
| `review_note` | text | ya | null | Catatan peninjau |
| `reviewed_at` | timestamp | ya | null | UTC |
| `cancelled_at` | timestamp | ya | null | UTC |
| `created_at` | timestamp | ya | null | |
| `updated_at` | timestamp | ya | null | |

Indeks:

- `leave_requests_tenant_status_index` pada `(tenant_id, status)`.
- `leave_requests_tenant_user_start_index` pada `(tenant_id, user_id, start_date)`.
- `leave_requests_tenant_range_index` pada `(tenant_id, start_date, end_date)`.

Aturan:

- `end_date >= start_date`.
- Rentang maksimal 30 hari inklusif.
- `start_date` tidak boleh sebelum hari ini saat pengajuan.
- Tumpang tindih dicek di service untuk status `pending` dan `approved`.
- Persetujuan membuat `attendance_days` untuk hari kerja terjadwal yang bukan
  hari libur.
- Pembatalan izin yang disetujui hanya untuk `start_date >= hari ini`.
- Hari presensi izin tanpa kejadian dihapus saat pembatalan. Hari dengan kejadian
  tidak dihapus dan pembatalan ditolak.

## 17. Tabel bawaan Laravel

### 17.1 sessions

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `id` | varchar(255) | Kunci utama |
| `user_id` | bigint unsigned | Null, indeks |
| `ip_address` | varchar(45) | Null |
| `user_agent` | text | Null |
| `payload` | longText | Data sesi |
| `last_activity` | integer | Indeks |

### 17.2 cache dan cache_locks

Dibuat oleh migrasi Laravel. Tidak dipakai untuk data domain v1. Boleh dipakai
untuk cache pengaturan dengan kunci berawalan `tenant:{id}:`.

### 17.3 jobs, job_batches, failed_jobs

Bawaan Laravel untuk antrean database. Worker wajib berjalan di produksi.

### 17.4 password_reset_tokens

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `email` | varchar(190) | Kunci utama |
| `token` | varchar(255) | Hash token |
| `created_at` | timestamp | Null |

## 18. Relasi

```
users 1---n memberships n---1 tenants
users 1---n invitations (invited_by)
tenants 1---1 tenant_settings
tenants 1---n locations
tenants 1---n divisions
tenants 1---n schedules
tenants 1---n holidays
tenants 1---n attendance_days
tenants 1---n attendance_events
tenants 1---n leave_requests

users 1---n schedules
users 1---n attendance_days
users 1---n attendance_events
users 1---n leave_requests

locations 1---n memberships (primary)
locations 1---n attendance_days
locations 1---n attendance_events

schedules 1---n attendance_days
leave_requests 1---n attendance_days
attendance_days 1---n attendance_events
attendance_events 1---n attendance_reviews
attendance_days 1---n attendance_corrections
```

Foreign key:

- Semua `tenant_id` memakai `cascadeOnDelete`.
- `memberships.user_id` memakai `cascadeOnDelete`.
- `memberships.primary_location_id` dan `division_id` memakai `nullOnDelete`.
- `attendance_days.schedule_id` dan `location_id` memakai `nullOnDelete`.
- `attendance_days.leave_request_id` memakai `nullOnDelete`.
- `attendance_events.attendance_day_id` memakai `nullOnDelete` agar kejadian
  tidak hilang bila hari dihapus karena pembatalan izin.
- `attendance_reviews.attendance_event_id` memakai `cascadeOnDelete`.
- `attendance_corrections.attendance_day_id` memakai `cascadeOnDelete`.

Catatan: penghapusan hari hanya terjadi pada pembatalan izin yang belum memiliki
kejadian. Untuk hari yang sudah memiliki kejadian, penghapusan ditolak di service.

## 19. Indeks dan alasan

| Indeks | Query yang dilayani |
| --- | --- |
| `attendance_days_tenant_date_status_index` | Daftar presensi harian, laporan |
| `attendance_days_tenant_user_date_unique` | Cek duplikasi, detail anggota |
| `attendance_events_tenant_user_date_index` | Riwayat percobaan |
| `attendance_events_attempt_uuid_unique` | Idempotensi pengiriman form |
| `schedules_tenant_user_date_unique` | Cek jadwal satu hari |
| `schedules_tenant_date_index` | Daftar jadwal harian |
| `memberships_tenant_role_index` | Daftar anggota per peran |
| `leave_requests_tenant_status_index` | Antrean izin |
| `holidays_tenant_date_unique` | Cek hari libur |

Tidak ada indeks pada `flags` karena JSON tidak diquery langsung di v1.

## 20. Idempotensi

- Setiap pengiriman form presensi membawa `attempt_uuid` yang dibuat di peramban
  saat halaman dimuat.
- Server mencoba menyimpan kejadian dengan UUID tersebut. Bila unique index
  menolak, server mengembalikan hasil yang sudah ada, bukan membuat kejadian baru.
- Finalisasi memakai unique `(tenant_id, user_id, work_date)` sehingga aman
  dijalankan berulang.
- Pembersihan selfie memakai `selfie_path` dan `updated_at`/waktu kejadian, aman
  diulang.

## 21. Retensi dan pembersihan

| Data | Retensi | Tindakan |
| --- | --- | --- |
| Selfie | 90 hari bawaan | Hapus berkas, kosongkan `selfie_path` |
| Sesi | Bawaan Laravel | Hapus sesi kedaluwarsa |
| Undangan kedaluwarsa | 30 hari setelah kedaluwarsa | Hapus baris |
| `failed_jobs` | 30 hari | Hapus berkas dan baris |
| Data presensi | Tidak dihapus | Arsip bila perlu |
| Hari izin yang dibatalkan | Segera | Hapus bila belum ada kejadian |

Pembersihan undangan dan `failed_jobs` dilakukan oleh command harian
`model:prune` atau command khusus. Dokumentasikan di `OPERATIONS.md`.

## 22. Data awal

### 22.1 Produksi

- Tidak ada tenant bawaan.
- Platform admin dibuat lewat perintah:

```bash
php artisan platform:promote admin@example.com
```

- Perintah membuat pengguna bila belum ada, atau menandai pengguna yang ada
  sebagai platform admin. Perintah menolak bila pengguna memiliki keanggotaan.

### 22.2 Lokal dan pengujian

Seeder lokal membuat:

- Satu tenant `Hadir.In Demo` dengan zona `Asia/Jakarta`, status aktif.
- Pemilik `pemilik@hadir.test`, admin `admin@hadir.test`, peninjau
  `peninjau@hadir.test`, anggota `anggota@hadir.test`.
- Kata sandi semua akun demo: `password123`.
- Dua lokasi: `Kantor Pusat` dan `Gudang Selatan`.
- Dua divisi: `Operasional` dan `Keuangan`.
- Jadwal Senin sampai Jumat untuk bulan berjalan.
- Beberapa hari presensi contoh untuk memperlihatkan status dan tanda.
- Satu platform admin `platform@hadir.test` dengan kata sandi `password123`.

Seeder demo tidak boleh dijalankan di produksi. Lindungi dengan pengecekan
`app()->environment('local', 'testing')`.

## 23. Migrasi

Urutan migrasi:

1. `0001_01_01_000000_create_users_table`
2. `0001_01_01_000001_create_cache_table`
3. `0001_01_01_000002_create_jobs_table`
4. `2026_10_30_000001_create_tenants_table`
5. `2026_10_30_000002_create_tenant_settings_table`
6. `2026_10_30_000003_create_locations_table`
7. `2026_10_30_000004_create_divisions_table`
8. `2026_10_30_000005_create_memberships_table`
9. `2026_10_30_000006_create_invitations_table`
10. `2026_10_30_000007_create_schedules_table`
11. `2026_10_30_000008_create_holidays_table`
12. `2026_10_30_000009_create_leave_requests_table`
13. `2026_10_30_000010_create_attendance_days_table`
14. `2026_10_30_000011_create_attendance_events_table`
15. `2026_10_30_000012_create_attendance_reviews_table`
16. `2026_10_30_000013_create_attendance_corrections_table`

Catatan urutan: `memberships` setelah `locations` dan `divisions` karena memiliki
foreign key ke keduanya. `attendance_days` setelah `leave_requests` dan
`schedules`. `attendance_events` setelah `attendance_days`.

Setiap migrasi wajib memiliki `down()` yang membalik urutan dengan benar.

## 24. Invariant yang diuji

1. Tidak ada dua anggota dengan email sama dalam satu tenant.
2. Tidak ada dua hari presensi untuk anggota dan tanggal yang sama.
3. Tidak ada dua jadwal untuk anggota dan tanggal yang sama.
4. Tidak ada dua hari libur pada tanggal yang sama dalam satu tenant.
5. Tidak ada dua undangan aktif untuk email yang sama dalam satu tenant.
6. Tidak ada dua tinjauan `pending` untuk kejadian yang sama.
7. Tidak ada dua koreksi `pending` untuk hari yang sama.
8. Setiap hari berstatus `hadir` atau `terlambat` memiliki `check_in_at`.
9. Setiap hari berstatus `izin`, `sakit`, atau `cuti` memiliki
   `leave_request_id`.
10. Setiap tenant memiliki satu owner aktif.
11. Setiap peninjau memiliki `primary_location_id`.
12. Tidak ada `membership` untuk pengguna dengan `is_platform_admin = true`.
13. Tidak ada data tenant A yang dikembalikan dalam query tenant B.
14. Tidak ada path selfie yang dapat diakses tanpa tanda tangan sah.

## 25. Contoh query penting

### 25.1 Presensi harian

```sql
select d.*, u.name, l.name as location_name
from attendance_days d
join users u on u.id = d.user_id
left join locations l on l.id = d.location_id
where d.tenant_id = ?
  and d.work_date = ?
order by u.name;
```

### 25.2 Ringkasan bulanan

```sql
select
  u.id,
  u.name,
  count(*) filter (where d.status in ('hadir', 'terlambat')) as hadir,
  count(*) filter (where d.status = 'terlambat') as terlambat,
  count(*) filter (where d.status in ('izin', 'sakit', 'cuti')) as izin,
  count(*) filter (where d.status = 'absen') as absen
from attendance_days d
join users u on u.id = d.user_id
where d.tenant_id = ?
  and d.work_date between ? and ?
group by u.id, u.name;
```

Query di atas memakai `filter`, yang tidak didukung SQLite versi lama. Untuk
portabilitas, gunakan `sum(case when ... then 1 else 0 end)` di implementasi.

### 25.3 Hari kerja terjadwal yang belum ada presensi

```sql
select s.user_id, s.work_date
from schedules s
where s.tenant_id = ?
  and s.work_date between ? and ?
  and s.is_day_off = false
  and not exists (
    select 1 from attendance_days d
    where d.tenant_id = s.tenant_id
      and d.user_id = s.user_id
      and d.work_date = s.work_date
  );
```

## 26. Catatan PostgreSQL vs SQLite

- PostgreSQL memakai `timestamp without time zone`; Laravel mengisi UTC.
- SQLite menyimpan tanggal dan waktu sebagai teks. Perbandingan tanggal dengan
  format `Y-m-d` tetap benar.
- `json` diperlakukan sebagai teks di SQLite dan `json` di PostgreSQL. Cast model
  wajib `array`.
- Unique index pada beberapa kolom bekerja di keduanya.
- Tidak memakai `ILIKE`, `DISTINCT ON`, `FILTER`, `GENERATED`, atau tipe array.
- Pencarian nama memakai `like` dengan nilai yang sudah di-lowercase bila perlu.
- Pengujian di SQLite tidak membuktikan perilaku konkurensi PostgreSQL. Uji beban
  dan konkurensi dilakukan di staging PostgreSQL.

## 27. Backup dan pemulihan data

- Backup logis harian: `pg_dump -Fc hadir > hadir-YYYY-MM-DD.dump`.
- Retensi 14 hari.
- Backup selfie mingguan dengan `tar` direktori `storage/app/private/selfies`.
- Uji pemulihan setiap kuartal di database terpisah.
- Jangan menyimpan backup di disk yang sama dengan database produksi.
- Enkripsi backup bila penyimpanan eksternal tidak tepercaya.

Perintah lengkap ada di `OPERATIONS.md`.
