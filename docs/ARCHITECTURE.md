# Arsitektur Hadir.In

Versi 1.0. Dokumen ini menjelaskan bagaimana aplikasi disusun dan mengapa.
Aturan produk ada di `PRD.md`. Skema data ada di `DATABASE.md`. Peta rute ada di
`ROUTES.md`.

## 1. Ringkasan

Hadir.In adalah aplikasi monolit Laravel 12 yang dirender di server dengan
Livewire 3 dan Alpine.js. Tidak ada API publik dan tidak ada aplikasi terpisah
untuk frontend. Satu basis kode melayani halaman publik, aplikasi anggota, layar
admin dan peninjau, serta panel platform.

Karakteristik utama:

- Monolit modular, bukan microservice.
- Satu database PostgreSQL 16 dengan kolom `tenant_id` dan global scope.
- Rendering server dengan Livewire untuk interaksi.
- Tailwind CSS 4 dengan token desain di CSS.
- Penyimpanan selfie privat di disk lokal.
- Antrean dan sesi di database.
- Penjadwalan lewat cron dan `schedule:run`.
- Pengujian Pest dengan SQLite di memori.

## 2. Konteks sistem

```
+-------------------+        HTTPS        +-----------------------------+
| Peramban pengguna | <-----------------> | Nginx                       |
| ponsel / desktop  |                     | TLS, static, fastcgi        |
+-------------------+                     +--------------+--------------+
                                                         |
                                                         v
                                          +-----------------------------+
                                          | PHP-FPM 8.3+                |
                                          | Laravel 12 + Livewire 3     |
                                          +------+--------------+-------+
                                                 |              |
                                                 v              v
                                     +-------------------+  +------------------+
                                     | PostgreSQL 16     |  | Disk privat      |
                                     | data operasional  |  | selfie           |
                                     +-------------------+  +------------------+
                                                 ^
                                                 |
                                     +-------------------+       +----------------+
                                     | Worker antrean    | <---- | Cron scheduler |
                                     | email, pembersihan|       | finalisasi     |
                                     +-------------------+       +----------------+
```

Tidak ada Redis, Elasticsearch, object storage, atau layanan pihak ketiga wajib.
Peta memakai tile OpenStreetMap dari peramban. Email memakai SMTP yang
dikonfigurasi. Bila SMTP belum ada, email masuk ke log dan tautan undangan tetap
ditampilkan di layar.

## 3. Stack dan alasan

| Lapisan | Pilihan | Alasan |
| --- | --- | --- |
| Bahasa | PHP 8.3+ | Tersedia luas di VPS Indonesia, ekosistem matang |
| Framework | Laravel 12 | Otentikasi, ORM, antrean, penjadwalan, validasi bawaan |
| Interaksi | Livewire 3 | Form dan tabel reaktif tanpa membangun API |
| Skrip kecil | Alpine.js | Bawaan Livewire, cukup untuk menu dan peta |
| CSS | Tailwind CSS 4 | Token di CSS, build cepat, tanpa runtime |
| Build | Vite 7 | Bawaan Laravel |
| Database | PostgreSQL 16 | Ketahanan, tipe data jelas, indeks baik |
| Pengujian | Pest | Sintaks ringkas di atas PHPUnit |
| Format | Laravel Pint | Standar gaya konsisten |
| Peta | Leaflet + OSM | Tanpa kunci API, ringan, dapat digayakan |
| Selfie | GD | Tersedia di PHP, cukup untuk resize dan strip EXIF |

Laravel 12 dipilih karena merupakan versi stabil saat proyek dimulai. Livewire 3
dipilih karena alur form panjang lebih mudah ditulis dan diuji daripada SPA
terpisah. Tailwind 4 dipakai melalui plugin Vite resmi.

## 4. Batas modul

Modul disusun berdasarkan domain, bukan tipe berkas. Setiap modul memiliki model,
service, policy, komponen Livewire, dan pengujiannya.

```
app/
  Actions/                 aksi tunggal yang mengubah state
    Attendance/
    Leave/
    Membership/
  Enums/                   status dan peran
  Http/
    Controllers/           autentikasi, undangan, ekspor, platform
    Middleware/            tenant, status, peran, platform
    Requests/              validasi form non-Livewire
  Livewire/
    Attendance/
    Auth/
    Dashboard/
    Leave/
    Locations/
    Members/
    Reports/
    Reviews/
    Schedules/
    Settings/
    Platform/
  Models/
  Policies/
  Services/
    AttendanceService.php
    GeofenceService.php
    ScheduleService.php
    LeaveService.php
    ReportService.php
    SelfieService.php
    TenantClock.php
  Support/
    TenantContext.php
    Flags.php
  Jobs/
  Console/Commands/
```

Aturan ketergantungan:

- Komponen Livewire memanggil action atau service. Komponen tidak menulis query
  domain yang rumit.
- Service tidak memanggil Livewire dan tidak memakai `request()`.
- Model hanya berisi relasi, cast, scope, dan aksesor sederhana.
- Policy memutuskan akses. Service memvalidasi aturan bisnis. Keduanya dipakai.
- Job dan command memakai service yang sama dengan antarmuka.

## 5. Multi-tenancy

### 5.1 Model

Satu database, satu skema, kolom `tenant_id` pada tabel operasional. Tidak memakai
row-level security PostgreSQL dan tidak memakai database per tenant.

Alasan: jumlah organisasi awal kecil, biaya operasional lebih rendah, migrasi
lebih sederhana, dan pengujian lebih mudah. Risiko kebocoran dikendalikan dengan
global scope, policy, route model binding, dan pengujian lintas tenant.

### 5.2 TenantContext

`App\Support\TenantContext` adalah singleton per request yang menyimpan:

- `Tenant $tenant`
- `Membership $membership`
- `User $user`

API:

```php
$context->set(Tenant $tenant, ?Membership $membership = null): void
$context->tenant(): ?Tenant
$context->membership(): ?Membership
$context->id(): ?int
$context->clear(): void
$context->hasTenant(): bool
```

Middleware `ResolveTenant` berjalan setelah `auth` pada grup rute aplikasi:

1. Baca `session('active_tenant_id')`.
2. Validasi bahwa pengguna memiliki keanggotaan aktif pada tenant tersebut.
3. Bila tidak valid, pilih keanggotaan aktif pertama.
4. Set context dan bagikan ke view.
5. Bila tidak ada keanggotaan, keluar dengan pesan yang jelas.

Middleware `EnsureTenantActive`:

- Tenant `active`: lanjut.
- Tenant `pending` atau `suspended`: pemilik diarahkan ke `/status`, anggota
  lain diarahkan ke `/status` dengan pesan bahwa organisasi belum aktif.

### 5.3 Trait BelongsToTenant

Trait ini:

- Menambahkan global scope `where tenant_id = context->id()` bila context ada.
- Mengisi `tenant_id` otomatis saat `creating`.
- Mencegah perubahan `tenant_id` setelah dibuat.
- Menyediakan relasi `tenant()`.

Model yang memakai trait: `Membership`, `Invitation`, `Location`, `Division`,
`Schedule`, `Holiday`, `AttendanceDay`, `AttendanceEvent`, `AttendanceReview`,
`AttendanceCorrection`, `LeaveRequest`, `Setting`.

Model yang tidak memakai trait: `User`, `Tenant`, `PasswordResetToken` (bawaan
Laravel), `Session`, `Job`, `FailedJob`.

Penting: `Tenant` sendiri tidak memakai scope. Akses ke `Tenant` hanya lewat
`TenantContext` atau middleware platform.

### 5.4 Konsekuensi dan pengaman

- Job yang berjalan tanpa request wajib memanggil `TenantContext::set()` sebelum
  menyentuh model tenant, dan `clear()` setelah selesai. Membership boleh null
  untuk job konsol; scope tetap aktif bila tenant diset.
- Pengujian kebocoran wajib membuat dua tenant dan memastikan query tenant A
  tidak mengembalikan data tenant B, termasuk lewat route model binding.
- `withoutGlobalScope` hanya boleh dipakai di panel platform, dengan komentar
  dan pengujian.
- Platform admin tidak memiliki context tenant. Query platform memakai model
  `Tenant` langsung dan hitungan agregat, bukan model operasional.

## 6. Siklus permintaan

### 6.0 Header keamanan
Setiap respons melewati `SecurityHeaders` yang dipasang sebagai middleware
global pada `bootstrap/app.php`, termasuk halaman galat, supaya satu rute baru
tidak pernah lolos tanpa header keamanan. Middleware ini menulis
`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`,
`Permissions-Policy`, `Cross-Origin-Opener-Policy`, dan
`Content-Security-Policy`. `Strict-Transport-Security` hanya ditulis bila
permintaan memakai HTTPS, supaya pengembangan di `localhost` tidak memaksa
peramban beralih ke HTTPS.

CSP `script-src` memakai nonce per permintaan. Middleware membuat nonce,
menyimpannya di `config('csp_nonce')` dan di `Vite::useCspNonce()`. Semua tag
`<script>` inline menerima nonce lewat helper `csp_script_nonce()` (tema,
JSON-LD), Vite menerapkannya pada tag build, dan `@livewireScripts` menerimanya
lewat argumen eksplisit. Livewire sendiri membaca nonce dari
`Vite::cspNonce()` dan dari atribut `<style nonce>` yang ia tulis sendiri.
`config('livewire.inject_assets')` diatur `false`, jadi keempat layout memanggil
`@livewireStyles` dan `@livewireScripts` secara eksplisit; injeksi otomatis
tidak bisa menerima argumen nonce.

`'unsafe-inline'` dan `'strict-dynamic'` tetap ada pada `script-src` sebagai
jaring pengaman untuk peramban lama: peramban modern yang mendukung
`strict-dynamic` mengabaikan keduanya, peramban lama yang tidak mendukung nonce
masih bisa menjalankan skrip. `style-src` masih `'unsafe-inline'` karena
Livewire/Alpine menyisipkan gaya inline tanpa nonce. CSP berfungsi menutup
frame, plugin, dan asal sumber daya asing.

### 6.1 Halaman publik
Enam halaman publik memakai `cache.headers:private;max_age=300;etag`. Peramban
dapat memakai ulang halaman dan menerima `304` bila isinya belum berubah. Cache
bersama (CDN) tidak dipakai karena respons masih membawa cookie sesi untuk
keadaan masuk pada navigasi. `/robots.txt` dan `/sitemap.xml` dihasilkan
`SeoController`.

`Route` -> controller atau komponen Livewire -> Blade -> Vite. Tanpa middleware
tenant. Contoh: beranda, masuk, daftar, undangan, privasi.

### 6.2 Halaman aplikasi

`Route` -> `auth` -> `ResolveTenant` -> `EnsureTenantActive` -> komponen Livewire
halaman penuh -> policy -> service -> model -> Blade.

Komponen Livewire halaman penuh memakai layout `layouts.app`. Setiap komponen
memanggil `authorize()` di `mount()` atau memakai policy di action.

### 6.3 Aksi tulis

1. Validasi input di komponen atau Form Request.
2. Otorisasi dengan policy.
3. Panggil action atau service.
4. Service membuka transaksi database.
5. Service menulis kejadian atau hari presensi.
6. Service mengembalikan hasil domain, bukan `Response`.
7. Komponen menampilkan toast dan memperbarui state.

### 6.4 Panel platform

`Route` -> `auth` -> `EnsurePlatformAdmin` -> komponen atau controller. Tidak ada
`ResolveTenant`. Layout `layouts.platform`. Semua query dibatasi pada data
organisasi, bukan data operasional.

## 7. Model waktu

Waktu adalah sumber kesalahan paling umum pada aplikasi presensi. Aturannya:

- `config('app.timezone')` adalah `UTC`.
- Database menyimpan `timestamp` tanpa zona waktu, dalam UTC.
- `tenants.timezone` menyimpan zona IANA, misalnya `Asia/Jakarta`.
- `work_date` adalah `date` lokal, bukan turunan dari timestamp saat query.
- `TenantClock` menyediakan:

```php
now(): CarbonImmutable            // sekarang di zona tenant
today(): string                   // Y-m-d lokal
combine(string $date, string $time): CarbonImmutable // UTC
toLocal(Carbon $utc): CarbonImmutable
formatDate(string $date): string
formatTime(?Carbon $utc): string
```

- Perbandingan waktu selalu dilakukan dalam UTC setelah menggabungkan tanggal
  lokal dan jam lokal.
- Tampilan selalu dikonversi ke zona tenant.
- Pengujian memakai `Carbon::setTestNow()` dengan waktu UTC yang eksplisit.

Contoh: jadwal 08.00 di `Asia/Jakarta` pada 30 Oktober 2026 disimpan sebagai
`2026-10-30 01:00:00` UTC. `work_date` tetap `2026-10-30`.

## 8. Alur presensi

### 8.1 Absen masuk

```
Komponen CheckIn
  -> AttendanceService::checkIn(user, input, selfie)
     -> TenantClock::now()
     -> cari jadwal (work_date)
     -> cek izin disetujui
     -> cek hari libur
     -> cek jadwal dan jendela waktu
     -> cek duplikasi
     -> GeofenceService::evaluate(lat, lng, accuracy)
     -> validasi selfie
     -> simpan file
     -> transaksi:
          finalisasi hari sebelumnya yang terbuka
          buat attendance_day
          buat attendance_event (accepted/rejected)
     -> kembalikan AttendanceResult
```

`AttendanceResult` berisi:

- `accepted: bool`
- `event: AttendanceEvent`
- `day: ?AttendanceDay`
- `status: ?AttendanceStatus`
- `flags: string[]`
- `reason: ?string`
- `message: string`
- `canRequestReview: bool`

Komponen menampilkan pesan dan tindakan sesuai hasil. Kejadian ditolak tetap
disimpan agar dapat ditinjau dan agar percobaan berulang terlihat.

### 8.2 Absen pulang

Alur serupa dengan tambahan:

- Wajib ada hari presensi dengan absen masuk.
- Wajib belum ada absen pulang.
- Selisih waktu masuk ke sekarang maksimal 24 jam.
- Tanda pulang cepat atau pulang terlambat dihitung, bukan penolakan.
- Hari diperbarui, bukan membuat hari baru.

### 8.3 Finalisasi

`FinalizeAttendance` command dijalankan setiap jam:

1. Ambil semua tenant aktif.
2. Untuk setiap tenant, set context.
3. Cari hari presensi yang belum final dan `scheduled_end + 240 menit` sudah
   lewat.
4. Hari dengan absen masuk: set `finalized_at`, tambah tanda `final_otomatis`
   bila belum ada pulang.
5. Hari tanpa absen masuk: buat hari `absen` bila hari kerja terjadwal, bukan
   libur, dan tidak ada izin disetujui.
6. Simpan ringkasan ke log.
7. Clear context.

Command idempoten. Menjalankan ulang tidak menggandakan hari karena unique index
`(tenant_id, user_id, work_date)`.

### 8.4 Sumber kebenaran status

- `attendance_days.status` menyimpan status utama: hadir, terlambat, izin, sakit,
  cuti, absen.
- `attendance_days.late_minutes` menyimpan menit keterlambatan.
- `attendance_days.early_leave_minutes` menyimpan menit pulang cepat.
- `attendance_days.flags` menyimpan array tanda.
- `attendance_days.finalized_at` menandai sudah difinalisasi.
- `attendance_events` menyimpan semua percobaan, termasuk yang ditolak.
- "Belum pulang" adalah tampilan turunan: ada masuk, belum ada pulang, belum
  final. Tidak disimpan sebagai status.
- "Libur" adalah tampilan turunan: tidak ada jadwal kerja atau jadwal `is_day_off`
  atau hari libur. Tidak disimpan sebagai status pada hari presensi.

## 9. Alur izin

```
Komponen LeaveForm
  -> LeaveService::submit(user, data)
     -> validasi rentang dan tumpang tindih
     -> validasi cuti pada hari kerja
     -> simpan leave_request (pending)
  -> notifikasi ke peninjau (database/email opsional)

Komponen LeaveReview
  -> LeaveService::approve(leave, reviewer, note)
     -> otorisasi lingkup
     -> cek absen pada rentang
     -> transaksi:
          set status approved
          buat attendance_days untuk hari kerja terjadwal
     -> simpan reviewed_at, reviewed_by
```

Pembatalan:

- Anggota: hanya status `pending`.
- Peninjau atau admin: status `approved`, hanya tanggal mulai hari ini atau
  masa depan. Hari presensi tanpa kejadian dihapus. Hari dengan kejadian tidak
  dihapus; pembatalan ditolak dan diarahkan ke koreksi.

## 10. Alur tinjauan dan koreksi

### 10.1 Tinjauan

- Dibuat dari kejadian ditolak.
- Status: `pending`, `approved`, `rejected`.
- Satu kejadian hanya boleh punya satu tinjauan `pending`.
- Persetujuan memanggil `AttendanceService::acceptReviewedEvent()` yang:
  - memastikan belum ada absen masuk pada hari itu,
  - membuat hari presensi dari kejadian,
  - menandai kejadian `accepted` dengan tanda `diterima_manual`,
  - menyimpan reviewer dan waktu.

### 10.2 Koreksi

- Menyimpan `original_values` dan `requested_values` sebagai JSON.
- Status: `pending`, `approved`, `rejected`.
- Persetujuan memanggil `AttendanceService::applyCorrection()`.
- Hari ditandai `is_corrected = true`.
- Kejadian asli tidak diubah selain penanda `corrected_at`.
- Koreksi yang disetujui tidak mengubah `tenant_id` atau `user_id`.

## 11. Penyimpanan selfie

- Disk: `private` (`storage/app/private`).
- Path: `selfies/{tenant_id}/{Y}/{m}/{uuid}.jpg`.
- Nama file UUID, bukan email atau nama pengguna.
- Proses: validasi MIME dengan `getimagesize` dan GD, baca gambar, hapus EXIF
  dengan membuat gambar baru, resize ke maksimal 1280 px, simpan JPEG kualitas 80.
- URL: route `selfie.show` dengan `URL::temporarySignedRoute(..., now()->addMinutes(5))`.
- Route memeriksa tanda tangan, lalu policy akses, lalu mengembalikan file.
- Retensi: command harian menghapus file yang lebih tua dari 90 hari dan
  mengosongkan `selfie_path` pada kejadian. Catatan tetap ada.
- Bila selfie dimatikan, kolom path kosong dan tidak ada file.

## 12. Antrean dan penjadwalan

Antrean memakai driver database. Pekerjaan antrean berupa Mailable yang
dikirim lewat `Mail::to(...)->queue()`:

| Pekerjaan | Pemicu | Sifat |
| --- | --- | --- |
| `SendInvitationEmail` | undangan dibuat | idempoten, aman diulang |
| `SendLeaveStatusEmail` | izin disetujui/ditolak | opsional |

Pembersihan dan finalisasi berjalan sebagai command terjadwal, bukan job
antrean, karena keduanya idempoten dan tidak perlu paralel pada v1:

| Command | Pemicu | Sifat |
| --- | --- | --- |
| `attendance:finalize` | setiap jam | idempoten, hanya organisasi aktif |
| `selfies:purge` | harian 02.00 | idempoten, hanya menghapus berkas selfie |

Penjadwalan di `routes/console.php`:

```php
Schedule::command('attendance:finalize')->hourly()->withoutOverlapping();
Schedule::command('selfies:purge')->dailyAt('02:00')->withoutOverlapping();
```

Zona waktu scheduler mengikuti `APP_TIMEZONE` (UTC). Command menentukan tanggal
kerja per tenant, bukan memakai tanggal server.

Produksi wajib menjalankan:

```
* * * * * cd /var/www/hadir && php artisan schedule:run >> /dev/null 2>&1
```

Worker antrean dijalankan supervisor dengan `--tries=3 --timeout=60`.

## 13. Keamanan

### 13.1 Otentikasi

- Sesi database, regenerasi ID saat login, invalidasi saat logout.
- Rate limit login 5 per menit per email dan IP.
- Kata sandi minimal 8 karakter, huruf dan angka, di-hash dengan bcrypt.
- Atur ulang kata sandi memakai token bawaan Laravel, berlaku 60 menit.
- Tidak ada verifikasi email di v1. Email dianggap unik dan sudah divalidasi
  formatnya.
- Tidak ada 2FA di v1. Dicatat sebagai batasan.

### 13.2 Otorisasi

- Policy per model: `MembershipPolicy`, `LocationPolicy`, `SchedulePolicy`,
  `AttendancePolicy`, `LeavePolicy`, `ReportPolicy`, `InvitationPolicy`.
- Gate `manageTenant` untuk admin dan pemilik.
- Gate `reviewAttendance` untuk admin, pemilik, dan peninjau.
- Gate `platformAdmin` hanya untuk `users.is_platform_admin`.
- Komponen Livewire memanggil `$this->authorize()` sebelum aksi.
- Route model binding memakai global scope tenant.

### 13.3 Perlindungan data

- CSRF pada semua form.
- Escape Blade otomatis. Tidak memakai `{!! !!}` untuk input pengguna.
- Validasi unggahan: MIME, ukuran, dimensi, dan ekstensi.
- Tidak menulis token undangan, kata sandi, atau path selfie ke log.
- Cookie sesi `HttpOnly`, `SameSite=Lax`, `Secure` di produksi.
- Header keamanan di Nginx: HSTS, X-Content-Type-Options, Referrer-Policy,
  X-Frame-Options.
- CSP dasar: `default-src 'self'`, `img-src 'self' data: blob: https://*.tile.openstreetmap.org`,
  `style-src 'self' 'unsafe-inline'`, `script-src 'self'`. Tile peta memerlukan
  koneksi ke OSM. Bila CSP ketat menyulitkan, dokumentasikan pengecualian.
- Platform admin tidak memiliki akses ke model operasional melalui policy.

### 13.4 Batas kepercayaan

- Data GPS dan waktu dari peramban tidak dipercaya sepenuhnya. Waktu server
  adalah sumber waktu resmi. Koordinat dipakai untuk validasi, tetapi akurasi
  dicatat dan dapat ditinjau.
- Selfie bukan bukti identitas kuat. Sistem tidak melakukan pengenalan wajah.
- Email undangan adalah identitas awal. Pemilik bertanggung jawab atas anggota
  yang diundang.

## 14. Kinerja

- Indeks utama: `(tenant_id, user_id, work_date)`, `(tenant_id, work_date)`,
  `(tenant_id, status)`, `(tenant_id, location_id)`.
- Laporan bulanan memakai query agregat, bukan memuat semua model ke PHP.
- Ekspor CSV memakai `cursor()` atau `lazy()` agar tidak memuat seluruh bulan ke
  memori.
- Eager load relasi yang dipakai tabel: `user`, `division`, `location`.
- Halaman daftar memakai pagination 25 baris.
- Peta dimuat hanya di halaman yang membutuhkan, dengan `@push('scripts')`.
- Aset produksi di-cache dengan hash nama berkas dari Vite.
- Tidak ada cache data presensi di v1. Bila perlu, cache hanya pengaturan tenant
  dengan kunci `tenant:{id}:settings` dan invalidasi saat pengaturan disimpan.

## 15. Pengamatan dan log

- Log aplikasi harian, retensi 14 hari.
- Log tidak memuat data pribadi berlebihan. Nama dan email boleh untuk konteks
  galat, selfie dan token tidak.
- Command finalisasi menulis ringkasan: tenant, jumlah hari difinalisasi, jumlah
  absen dibuat, durasi.
- Kegagalan job masuk `failed_jobs` dan dipantau.
- Health check sederhana: `GET /health` mengembalikan `200` dan status database.
  Tidak menampilkan detail sensitif.
- Metrik yang disarankan: waktu respons, jumlah 5xx, panjang antrean, kegagalan
  job, dan jumlah presensi per hari.

## 16. Penyebaran

Topologi produksi v1:

- Satu VPS dengan Nginx, PHP-FPM, PostgreSQL, worker, dan cron.
- TLS dengan Certbot.
- Kode di `/var/www/hadir`, rilis baru lewat deploy sederhana dan symlink.
- Aset dibangun di server atau di CI lalu diunggah.
- Backup database harian dengan `pg_dump`, disimpan 14 hari.
- Backup file selfie mingguan, disimpan 4 minggu.
- Tidak memakai Docker di produksi v1 agar operasi sederhana.

Detail langkah ada di `OPERATIONS.md`.

## 17. Keputusan dan konsekuensi

| Keputusan | Konsekuensi |
| --- | --- |
| Monolit Laravel + Livewire | Cepat dibangun, mudah diuji, sulit diskalakan horizontal tanpa session store bersama |
| Satu database + tenant_id | Murah dan sederhana, risiko kebocoran harus dikendalikan kode |
| Tanpa RLS | Tidak bergantung fitur PostgreSQL, tetapi tidak ada jaring pengaman di level database |
| Selfie di disk lokal | Sederhana dan privat, tidak cocok untuk banyak server tanpa storage bersama |
| Antrean database | Tanpa Redis, tetapi beban database naik saat antrean panjang |
| SQLite untuk tes | Cepat dan portabel, tetapi tidak menguji fitur PostgreSQL |
| Tanpa API publik | Ruang lingkup kecil, integrasi eksternal terbatas |
| Tanpa audit log | Lebih sedikit tabel, jejak perubahan terbatas pada koreksi dan event |

## 18. Batasan yang diketahui

1. PHP lokal pengembang mungkin tidak memiliki `pdo_pgsql`. Pengujian memakai
   SQLite. Perbedaan perilaku PostgreSQL harus diuji di staging.
2. Tidak ada dukungan shift lintas tengah malam.
3. Tidak ada absensi offline.
4. Tidak ada notifikasi push.
5. Tidak ada pengenalan wajah atau liveness.
6. Tidak ada API publik.
7. Tidak ada 2FA.
8. Tidak ada audit log umum.
9. Satu VPS adalah titik gagal tunggal.
10. Peta bergantung pada ketersediaan tile OpenStreetMap.

## 19. Panduan perluasan

- Modul baru mengikuti pola: model + migration + policy + service + komponen +
  pengujian + dokumen.
- Jangan menambah kolom status baru tanpa memperbarui `DATABASE.md` dan
  `PRD.md`.
- Untuk menambah jenis izin, tambahkan nilai enum, label, warna di `DESIGN.md`,
  dan aturan di `LeaveService`.
- Untuk menambah laporan, buat `ReportService` baru dan route terpisah. Jangan
  menaruh logika laporan di komponen.
- Untuk memindahkan selfie ke object storage, ganti `SelfieService` dan
  konfigurasi disk. Jangan mengubah model.
- Untuk multi-server, pindahkan sesi dan antrean ke Redis serta selfie ke S3.
  Perubahan ini memerlukan ADR baru.
