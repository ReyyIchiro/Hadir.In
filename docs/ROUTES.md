# Rute Hadir.In

Versi 1.0. Semua rute memakai HTTPS di produksi. Nama rute memakai titik dan
huruf kecil. Parameter rute memakai camelCase pada nama dan kebab-case pada URL.

## 1. Konvensi

- Rute publik tanpa middleware khusus.
- Rute aplikasi memakai prefix `/app`, middleware `auth`, `tenant`, `tenant.active`.
- Rute platform memakai prefix `/platform`, middleware `auth`, `platform`.
- Aksi yang mengubah state memakai `POST`, `PUT`, `PATCH`, atau `DELETE`.
- Halaman yang hanya menampilkan data memakai `GET`.
- Livewire memakai komponen halaman penuh untuk `/app` dan `/platform`. Aksi di
  dalam komponen tidak memiliki rute HTTP sendiri.
- Autentikasi, undangan, ekspor CSV, selfie bertanda tangan, dan health check
  memakai controller.
- Semua nama rute yang dipakai di Blade harus terdaftar di dokumen ini.

Middleware alias:

| Alias | Kelas | Fungsi |
| --- | --- | --- |
| `auth` | bawaan | Wajib masuk |
| `guest` | bawaan | Hanya untuk tamu |
| `tenant` | `ResolveTenant` | Menentukan tenant aktif |
| `tenant.active` | `EnsureTenantActive` | Memblokir tenant belum aktif |
| `platform` | `EnsurePlatformAdmin` | Hanya platform admin |
| `throttle:login` | `RateLimiter` | 5 percobaan per menit |

## 2. Rute publik

| Method | URL | Nama | Handler | Keterangan |
| --- | --- | --- | --- | --- |
| GET | `/` | `home` | `PublicPageController@home` | Beranda |
| GET | `/cara-kerja` | `public.how` | `PublicPageController@how` | Cara kerja |
| GET | `/untuk-siapa` | `public.who` | `PublicPageController@who` | Untuk siapa |
| GET | `/pertanyaan` | `public.faq` | `PublicPageController@faq` | Pertanyaan |
| GET | `/privasi` | `public.privacy` | `PublicPageController@privacy` | Kebijakan privasi |
| GET | `/ketentuan` | `public.terms` | `PublicPageController@terms` | Ketentuan layanan |
| GET | `/masuk` | `login` | `Auth\LoginController@show` | Form masuk |
| POST | `/masuk` | `login.store` | `Auth\LoginController@store` | Proses masuk |
| POST | `/keluar` | `logout` | `Auth\LoginController@destroy` | Keluar |
| GET | `/daftar` | `register` | `Auth\RegisterController@show` | Form daftar |
| POST | `/daftar` | `register.store` | `Auth\RegisterController@store` | Proses daftar |
| GET | `/lupa-kata-sandi` | `password.request` | `Auth\PasswordResetController@request` | Form lupa |
| POST | `/lupa-kata-sandi` | `password.email` | `Auth\PasswordResetController@send` | Kirim tautan |
| GET | `/atur-ulang-kata-sandi/{token}` | `password.reset` | `Auth\PasswordResetController@edit` | Form atur ulang |
| POST | `/atur-ulang-kata-sandi` | `password.update` | `Auth\PasswordResetController@update` | Simpan kata sandi |
| GET | `/undangan/{token}` | `invitations.show` | `InvitationController@show` | Lihat undangan |
| POST | `/undangan/{token}` | `invitations.accept` | `InvitationController@accept` | Terima undangan |
| GET | `/status` | `tenant.status` | `TenantStatusController@show` | Status organisasi |
| GET | `/health` | `health` | `HealthController@show` | Health check |

Catatan:

- `/status` memakai middleware `auth` tanpa `tenant.active`.
- `/health` hanya mengembalikan `{"status":"ok","database":"ok"}` atau `503`.
- Halaman publik memakai layout `layouts.public`.
- Halaman autentikasi memakai layout `layouts.auth`.

## 3. Rute anggota dan aplikasi

Semua rute di bagian ini memakai middleware `auth`, `tenant`, `tenant.active`.
Komponen Livewire halaman penuh ditulis sebagai `Component::class`.

### 3.1 Beranda dan presensi

| Method | URL | Nama | Handler | Keterangan |
| --- | --- | --- | --- | --- |
| GET | `/app` | `app.home` | `Dashboard\Home` | Beranda sesuai peran |
| GET | `/app/ringkasan` | `app.overview` | `Dashboard\Overview` | Ringkasan admin dan peninjau |
| GET | `/app/presensi` | `app.attendance.index` | `Attendance\Index` | Riwayat anggota atau daftar harian |
| GET | `/app/presensi/masuk` | `app.attendance.check-in` | `Attendance\CheckIn` | Layar absen masuk |
| GET | `/app/presensi/pulang` | `app.attendance.check-out` | `Attendance\CheckOut` | Layar absen pulang |
| GET | `/app/presensi/{attendanceDay}` | `app.attendance.show` | `Attendance\Show` | Detail hari presensi |

Catatan:

- `/app/presensi` menampilkan riwayat pribadi untuk anggota. Untuk admin dan
  peninjau, halaman yang sama menampilkan daftar harian dengan filter.
- `/app/presensi/{attendanceDay}` memakai route model binding dengan global scope
  tenant dan policy `view`.
- Koreksi diajukan lewat komponen Livewire di halaman detail. Tidak ada rute POST
  terpisah dan tidak ada halaman daftar koreksi tersendiri. Riwayat koreksi tampil
  di detail hari presensi.

### 3.2 Jadwal

| Method | URL | Nama | Handler | Keterangan |
| --- | --- | --- | --- | --- |
| GET | `/app/jadwal` | `app.schedules.index` | `Schedules\Index` | Jadwal mingguan |
| GET | `/app/jadwal/terapkan` | `app.schedules.bulk` | `Schedules\BulkApply` | Terapkan massal |
| GET | `/app/jadwal/{schedule}/ubah` | `app.schedules.edit` | `Schedules\Edit` | Ubah satu jadwal |

### 3.3 Izin dan cuti

| Method | URL | Nama | Handler | Keterangan |
| --- | --- | --- | --- | --- |
| GET | `/app/izin` | `app.leave.index` | `Leave\Index` | Daftar izin |
| GET | `/app/izin/baru` | `app.leave.create` | `Leave\Create` | Form pengajuan |
| GET | `/app/izin/{leaveRequest}` | `app.leave.show` | `Leave\Show` | Detail pengajuan |

Aksi setujui, tolak, dan batalkan dilakukan di dalam komponen Livewire pada
halaman detail atau daftar.

### 3.4 Tinjauan

| Method | URL | Nama | Handler | Keterangan |
| --- | --- | --- | --- | --- |
| GET | `/app/tinjauan` | `app.reviews.index` | `Reviews\Index` | Antrean tinjauan |
| GET | `/app/tinjauan/presensi/{review}` | `app.reviews.attendance` | `Reviews\AttendanceReview` | Detail tinjauan presensi |
| GET | `/app/tinjauan/koreksi/{correction}` | `app.reviews.correction` | `Reviews\CorrectionReview` | Detail koreksi |
| GET | `/app/tinjauan/izin/{leaveRequest}` | `app.reviews.leave` | `Reviews\LeaveReview` | Detail izin |

Middleware tambahan: `can:review-attendance` pada grup tinjauan.

### 3.5 Laporan

| Method | URL | Nama | Handler | Keterangan |
| --- | --- | --- | --- | --- |
| GET | `/app/laporan` | `app.reports.index` | `Reports\Index` | Laporan bulanan |
| GET | `/app/laporan/ekspor` | `app.reports.export` | `ReportExportController@csv` | Unduh CSV |

Parameter ekspor: `month` (`YYYY-MM`), `division_id`, `location_id`. Validasi:
bulan wajib format benar dan dalam 24 bulan terakhir, filter opsional dan harus
milik tenant.

### 3.6 Anggota dan undangan

| Method | URL | Nama | Handler | Keterangan |
| --- | --- | --- | --- | --- |
| GET | `/app/anggota` | `app.members.index` | `Members\Index` | Daftar anggota |
| GET | `/app/anggota/undang` | `app.members.invite` | `Members\Invite` | Form undangan |
| GET | `/app/anggota/undangan` | `app.invitations.index` | `Members\Invitations` | Undangan tertunda |
| GET | `/app/anggota/{membership}/ubah` | `app.members.edit` | `Members\Edit` | Ubah anggota |

Middleware tambahan: `can:manage-members` pada grup anggota.

### 3.7 Lokasi dan divisi

| Method | URL | Nama | Handler | Keterangan |
| --- | --- | --- | --- | --- |
| GET | `/app/lokasi` | `app.locations.index` | `Locations\Index` | Daftar lokasi |
| GET | `/app/lokasi/baru` | `app.locations.create` | `Locations\Form` | Tambah lokasi |
| GET | `/app/lokasi/{location}/ubah` | `app.locations.edit` | `Locations\Form` | Ubah lokasi |
| GET | `/app/divisi` | `app.divisions.index` | `Divisions\Index` | Daftar divisi |

Middleware tambahan: `can:manage-settings`.

### 3.8 Hari libur

| Method | URL | Nama | Handler | Keterangan |
| --- | --- | --- | --- | --- |
| GET | `/app/hari-libur` | `app.holidays.index` | `Holidays\Index` | Daftar hari libur |

Middleware tambahan: `can:manage-settings`.

### 3.9 Pengaturan dan profil

| Method | URL | Nama | Handler | Keterangan |
| --- | --- | --- | --- | --- |
| GET | `/app/pengaturan` | `app.settings.index` | `Settings\Index` | Pengaturan organisasi |
| GET | `/app/profil` | `app.profile` | `Settings\Profile` | Profil sendiri |
| GET | `/app/tampilan` | `app.appearance` | `Settings\Appearance` | Tema dan preferensi |

Middleware tambahan: `can:manage-settings` hanya untuk `/app/pengaturan`.

### 3.10 Pindah organisasi

| Method | URL | Nama | Handler | Keterangan |
| --- | --- | --- | --- | --- |
| POST | `/app/organisasi/{tenant}/pilih` | `app.tenants.switch` | `TenantSwitchController@store` | Pilih organisasi aktif |

Validasi: pengguna harus memiliki keanggotaan aktif pada tenant tujuan. Tenant
harus aktif, kecuali pengguna adalah pemilik tenant `pending`.

## 4. Rute platform

Semua rute memakai middleware `auth`, `platform`. Layout `layouts.platform`.
Tidak memakai `tenant` atau `tenant.active`.

| Method | URL | Nama | Handler | Keterangan |
| --- | --- | --- | --- | --- |
| GET | `/platform` | `platform.dashboard` | `Platform\Dashboard` | Ringkasan platform |
| GET | `/platform/tenant` | `platform.tenants.index` | `Platform\Tenants\Index` | Daftar tenant |
| GET | `/platform/tenant/{tenant}` | `platform.tenants.show` | `Platform\Tenants\Show` | Detail tenant |
| POST | `/platform/tenant/{tenant}/aktifkan` | `platform.tenants.activate` | `Platform\Tenants\Show@activate` | Aktifkan |
| POST | `/platform/tenant/{tenant}/tangguhkan` | `platform.tenants.suspend` | `Platform\Tenants\Show@suspend` | Tangguhkan |
| POST | `/platform/tenant/{tenant}/aktifkan-kembali` | `platform.tenants.restore` | `Platform\Tenants\Show@restore` | Aktifkan kembali |

Catatan:

- Platform admin tidak dapat membuka `/app`. Middleware `tenant` akan menolak
  karena tidak ada keanggotaan.
- Detail tenant hanya menampilkan nama, slug, zona waktu, status, paket, tanggal
  dibuat, jumlah anggota aktif, dan jumlah undangan tertunda. Tidak ada data
  presensi, selfie, atau daftar anggota.
- Aksi status memakai konfirmasi modal.

## 5. Rute selfie

| Method | URL | Nama | Handler | Keterangan |
| --- | --- | --- | --- | --- |
| GET | `/selfie/{event}` | `selfie.show` | `SelfieController@show` | Tampilkan selfie |

Middleware: `auth`, `tenant`, `signed`. URL dibuat dengan masa berlaku 5 menit.
Policy memeriksa:

- Anggota pemilik kejadian.
- Admin atau pemilik tenant.
- Peninjau dalam lingkup lokasi.
- Platform admin selalu ditolak.

Respons: `image/jpeg` dengan `Cache-Control: private, no-store`.

## 6. Rute cadangan

| Method | URL | Nama | Handler |
| --- | --- | --- | --- |
| GET | `/api/*` | - | 404. Tidak ada API publik v1. |

Tidak ada rute `/bahasa`. Antarmuka hanya berbahasa Indonesia.

## 7. Ringkasan middleware per grup

```php
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'show'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])
        ->middleware('throttle:login')->name('login.store');
    Route::get('/daftar', [RegisterController::class, 'show'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/status', TenantStatusController::class)->name('tenant.status');
});

Route::middleware(['auth', 'tenant', 'tenant.active'])
    ->prefix('app')
    ->name('app.')
    ->group(function () {
        // halaman aplikasi
    });

Route::middleware(['auth', 'platform'])
    ->prefix('platform')
    ->name('platform.')
    ->group(function () {
        // halaman platform
    });
```

## 8. Peta nama rute ke komponen

| Nama rute | Komponen Livewire | Policy |
| --- | --- | --- |
| `app.home` | `Dashboard\Home` | - |
| `app.overview` | `Dashboard\Overview` | `viewAny` laporan |
| `app.attendance.index` | `Attendance\Index` | `viewAny` presensi |
| `app.attendance.check-in` | `Attendance\CheckIn` | `create` presensi |
| `app.attendance.check-out` | `Attendance\CheckOut` | `checkOut` presensi |
| `app.attendance.show` | `Attendance\Show` | `view` presensi |
| `app.schedules.index` | `Schedules\Index` | `viewAny` jadwal |
| `app.schedules.bulk` | `Schedules\BulkApply` | `create` jadwal |
| `app.schedules.edit` | `Schedules\Edit` | `update` jadwal |
| `app.leave.index` | `Leave\Index` | `viewAny` izin |
| `app.leave.create` | `Leave\Create` | `create` izin |
| `app.leave.show` | `Leave\Show` | `view` izin |
| `app.reviews.index` | `Reviews\Index` | `reviewAttendance` |
| `app.reviews.attendance` | `Reviews\AttendanceReview` | `reviewAttendance` |
| `app.reviews.correction` | `Reviews\CorrectionReview` | `reviewAttendance` |
| `app.reviews.leave` | `Reviews\LeaveReview` | `reviewAttendance` |
| `app.reports.index` | `Reports\Index` | `viewReports` |
| `app.members.index` | `Members\Index` | `manageMembers` |
| `app.members.invite` | `Members\Invite` | `manageMembers` |
| `app.members.edit` | `Members\Edit` | `manageMembers` |
| `app.invitations.index` | `Members\Invitations` | `manageMembers` |
| `app.locations.index` | `Locations\Index` | `manageSettings` |
| `app.locations.create` | `Locations\Form` | `manageSettings` |
| `app.locations.edit` | `Locations\Form` | `manageSettings` |
| `app.divisions.index` | `Divisions\Index` | `manageSettings` |
| `app.holidays.index` | `Holidays\Index` | `manageSettings` |
| `app.settings.index` | `Settings\Index` | `manageSettings` |
| `app.profile` | `Settings\Profile` | - |
| `app.appearance` | `Settings\Appearance` | - |
| `platform.dashboard` | `Platform\Dashboard` | `platformAdmin` |
| `platform.tenants.index` | `Platform\Tenants\Index` | `platformAdmin` |
| `platform.tenants.show` | `Platform\Tenants\Show` | `platformAdmin` |

## 9. Catatan implementasi rute

- Livewire 3 halaman penuh didaftarkan langsung sebagai kelas komponen:
  `Route::get('/app', Dashboard\Home::class)->name('app.home');`
- Aksi di dalam komponen tidak membutuhkan rute POST. Koreksi, persetujuan, dan
  pembatalan semuanya aksi komponen Livewire, bukan rute terpisah.
- Route model binding untuk model tenant memakai global scope. Bila model tidak
  ditemukan dalam tenant aktif, Laravel mengembalikan 404, bukan 403. Ini
  disengaja agar keberadaan data tenant lain tidak terbocor.
- Nama parameter route memakai camelCase pada method controller, tetapi segmen
  URL memakai kebab-case. Contoh: `{attendanceDay}` menjadi
  `/app/presensi/{attendanceDay}`.
- Untuk `/app/presensi`, parameter bulan dan filter memakai query string, bukan
  segmen URL. Contoh: `/app/presensi?bulan=2026-10&status=terlambat`.
- Untuk laporan, parameter `month` memakai format `YYYY-MM`. Bila tidak valid,
  kembalikan 422 atau redirect dengan pesan galat. Jangan diam-diam memakai bulan
  berjalan.
- Semua rute GET yang menampilkan data sensitif memakai `no-store` pada header
  respons bila memuat selfie atau detail pribadi.

## 10. Rute yang sengaja tidak ada

- Tidak ada pendaftaran platform admin lewat web.
- Tidak ada rute hapus tenant.
- Tidak ada rute hapus data presensi.
- Tidak ada rute API publik.
- Tidak ada rute unggah massal anggota.
- Tidak ada rute impor CSV.
- Tidak ada rute ekspor PDF.
- Tidak ada rute notifikasi push.
- Tidak ada rute multi-bahasa.
- Tidak ada rute ubah email undangan.
- Tidak ada rute login sosial.

## 11. Status HTTP yang dipakai

| Kode | Pemakaian |
| --- | --- |
| 200 | Halaman berhasil |
| 302 | Redirect setelah aksi |
| 403 | Tidak berwenang |
| 404 | Data tidak ditemukan dalam tenant |
| 419 | CSRF kedaluwarsa |
| 422 | Validasi gagal |
| 429 | Terlalu banyak percobaan |
| 503 | Health check gagal atau mode pemeliharaan |

Galat 403 dan 404 memakai halaman galat yang tenang, bukan tumpukan debug.
Halaman 419 menjelaskan bahwa sesi kedaluwarsa dan meminta pengguna memuat ulang.
