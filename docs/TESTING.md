# Pengujian Hadir.In

Versi 1.0. Pengujian memakai Pest di atas PHPUnit. Tes berjalan dengan SQLite di
memori agar cepat dan tidak bergantung pada PostgreSQL lokal. Perilaku khusus
PostgreSQL diuji di staging.

## 1. Tujuan

- Membuktikan aturan bisnis presensi, izin, dan laporan berjalan benar.
- Membuktikan tidak ada kebocoran data antar organisasi.
- Membuktikan peran dan policy bekerja.
- Membuktikan berkas selfie tidak dapat diakses tanpa izin.
- Menjaga agar perubahan berikutnya tidak merusak perilaku yang sudah disepakati.

## 2. Perintah

```bash
php artisan test
php artisan test --filter=CheckInTest
php artisan test tests/Feature/Attendance
./vendor/bin/pint --test
npm run build
```

Konfigurasi `phpunit.xml`:

```xml
<env name="APP_ENV" value="testing"/>
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
<env name="CACHE_STORE" value="array"/>
<env name="QUEUE_CONNECTION" value="sync"/>
<env name="SESSION_DRIVER" value="array"/>
<env name="MAIL_MAILER" value="array"/>
<env name="BCRYPT_ROUNDS" value="4"/>
```

`APP_TIMEZONE` tetap `UTC`. Setiap tes yang menyentuh waktu memanggil
`Carbon::setTestNow()` dengan waktu UTC eksplisit.

## 3. Struktur

```
tests/
  Pest.php
  TestCase.php
  Feature/
    Auth/
      RegistrationTest.php
      LoginTest.php
      PasswordResetTest.php
    Tenancy/
      TenantIsolationTest.php
      TenantSwitchTest.php
    Invitations/
      InvitationFlowTest.php
    Attendance/
      CheckInTest.php
      CheckOutTest.php
      GeofenceTest.php
      FinalizationTest.php
      CorrectionTest.php
      ReviewTest.php
    Leave/
      LeaveSubmissionTest.php
      LeaveApprovalTest.php
    Reports/
      MonthlyReportTest.php
      CsvExportTest.php
    Platform/
      TenantStatusTest.php
      PlatformAccessTest.php
    Settings/
      SettingsTest.php
  Unit/
    GeofenceServiceTest.php
    TenantClockTest.php
    AttendanceCalculatorTest.php
```

## 4. Helper pengujian

`tests/Pest.php` menyediakan:

```php
function makeTenant(array $attributes = []): Tenant
function makeUser(array $attributes = []): User
function makeMembership(Tenant $tenant, User $user, string $role = 'member', array $attributes = []): Membership
function actingAsMember(Tenant $tenant, array $attributes = []): User
function actingAsAdmin(Tenant $tenant, array $attributes = []): User
function actingAsReviewer(Tenant $tenant, Location $location, array $attributes = []): User
function setTenant(Tenant $tenant): void
function makeSchedule(Tenant $tenant, User $user, string $date, string $start = '08:00', string $end = '17:00'): Schedule
function makeLocation(Tenant $tenant, array $attributes = []): Location
function freezeAt(string $utc): void
```

Aturan helper:

- Selalu membuat data dengan `tenant_id` eksplisit.
- Mengembalikan model, bukan array.
- Tidak melakukan assertion.
- Tidak memakai nama pengguna nyata atau email domain nyata. Gunakan
  `@hadir.test`.

## 5. Kasus kritis

### 5.1 Autentikasi dan pendaftaran

| Tes | Harapan |
| --- | --- |
| Daftar dengan data valid | Tenant `pending`, owner dibuat, settings dibuat, pengguna masuk |
| Daftar dengan email duplikat | 422, tidak ada tenant baru |
| Daftar tanpa nama organisasi | 422 |
| Daftar dengan zona waktu tidak valid | 422 |
| Login benar | Redirect ke `/app` atau `/status` |
| Login salah 5 kali | 429 pada percobaan keenam |
| Login anggota tenant pending | Ditolak dengan pesan status |
| Login pemilik tenant pending | Diarahkan ke `/status` |
| Logout | Sesi dihapus, diarahkan ke beranda |
| Atur ulang kata sandi | Token valid mengubah kata sandi, token tidak dapat dipakai ulang |

### 5.2 Tenancy

| Tes | Harapan |
| --- | --- |
| Query `AttendanceDay` tenant A | Tidak mengembalikan data tenant B |
| Route model binding hari tenant B | 404 untuk pengguna tenant A |
| Pindah tenant | Session berubah, data tenant baru terlihat |
| Pindah ke tenant tanpa keanggotaan | 403 |
| Pindah ke tenant suspended sebagai anggota | 403 |
| Platform admin membuka `/app` | 403 atau redirect, bukan data |
| Dua tenant dengan nama anggota sama | Tidak saling terlihat |

### 5.3 Undangan

| Tes | Harapan |
| --- | --- |
| Undang email baru | Token dibuat, email dikirim, tautan tampil |
| Token kedaluwarsa | 410 atau halaman galat yang jelas |
| Token dipakai dua kali | Percobaan kedua ditolak |
| Email undangan diubah | Ditolak, email terkunci |
| Undangan untuk email terdaftar | Wajib login dengan akun tersebut |
| Undangan untuk email baru | Akun dibuat, keanggotaan dibuat |
| Undangan dicabut | Tautan tidak dapat dipakai |
| Admin mengundang admin | Ditolak |
| Admin mengundang owner | Ditolak |
| Reviewer diundang tanpa lokasi | Validasi gagal |

### 5.4 Absen masuk

Gunakan tenant `Asia/Jakarta`, jadwal 08.00 sampai 17.00, dan waktu UTC yang
setara.

| Skenario | Waktu lokal | Harapan |
| --- | --- | --- |
| Tepat waktu | 07.55 | Diterima, status `hadir` |
| Tepat di jam mulai | 08.00 | Diterima, status `hadir` |
| Toleransi | 08.10 | Diterima, status `hadir`, late 0 |
| Terlambat | 08.11 | Diterima, status `terlambat`, late 11 |
| Terlambat jauh | 09.30 | Diterima, status `terlambat`, late 90 |
| Terlalu awal | 06.59 | Ditolak, alasan `terlalu_awal` |
| Batas awal | 07.00 | Diterima |
| Setelah jam selesai | 17.01 | Ditolak, alasan `jadwal_berakhir` |
| Hari libur | 08.00 | Diterima, status `hadir`, tanda `hari_libur` |
| Tanpa jadwal, izin mati | 08.00 | Ditolak, alasan `tidak_ada_jadwal` |
| Tanpa jadwal, izin hidup | 08.00 | Diterima, tanda `tanpa_jadwal` |
| Jadwal day off, izin mati | 08.00 | Ditolak, alasan `hari_libur_jadwal` |
| Izin disetujui | 08.00 | Ditolak, alasan `izin_disetujui` |
| Sudah absen masuk | 08.05 | Ditolak, alasan `sudah_masuk` |
| GPS di luar radius dan buffer | 08.00 | Ditolak, alasan `di_luar_radius` |
| GPS dalam buffer | 08.00 | Diterima, tanda `di_luar_radius` |
| Akurasi 150 m | 08.00 | Diterima, tanda `akurasi_rendah` |
| Tanpa lokasi aktif | 08.00 | Diterima, tanda `tanpa_lokasi` |
| GPS wajib tetapi kosong | 08.00 | Ditolak, alasan `lokasi_tidak_tersedia` |
| GPS tidak wajib dan kosong | 08.00 | Diterima, tanda `tanpa_lokasi` |
| Selfie wajib tetapi kosong | 08.00 | Ditolak, alasan `selfie_wajib` |
| Selfie bukan gambar | 08.00 | Ditolak, alasan `selfie_tidak_valid` |
| Attempt UUID sama | 08.00 | Hanya satu kejadian |

Setiap tes memeriksa:

- Jumlah `attendance_events`.
- Jumlah `attendance_days`.
- Status dan `late_minutes`.
- Array `flags`.
- Alasan penolakan.
- Tidak ada file selfie yang tertinggal saat ditolak sebelum penyimpanan.

### 5.5 Absen pulang

| Skenario | Harapan |
| --- | --- |
| Pulang normal 17.00 | Diterima, `check_out_at` terisi |
| Pulang 16.45 | Diterima, tanda `pulang_cepat`, early 15 |
| Pulang 16.51 | Diterima, tanpa tanda pulang cepat |
| Pulang 21.00 | Diterima, tanda `pulang_terlambat` |
| Pulang 21.01 | Diterima, tanda `pulang_terlambat` |
| Pulang 24 jam lebih | Ditolak, alasan `melewati_batas_pulang` |
| Pulang tanpa masuk | Ditolak, alasan `belum_absen_masuk` |
| Pulang dua kali | Ditolak, alasan `sudah_pulang` |
| Pulang pada hari yang sudah final | Ditolak atau memerlukan koreksi |

### 5.6 Finalisasi

| Skenario | Harapan |
| --- | --- |
| Hari kerja tanpa absen, sudah lewat | Hari `absen` dibuat |
| Hari kerja tanpa absen, belum lewat | Tidak ada hari dibuat |
| Hari libur tanpa absen | Tidak ada hari `absen` |
| Hari dengan izin disetujui | Tidak ada hari `absen` |
| Hari dengan masuk tanpa pulang | Final, tanda `final_otomatis` dan `tanpa_pulang` |
| Hari dengan masuk dan pulang | Final, tanpa tanda tambahan |
| Finalisasi dua kali | Tidak menggandakan hari |
| Absen masuk hari baru | Hari sebelumnya yang terbuka difinalisasi |
| Hari `is_day_off` | Tidak ada hari `absen` |
| Tenant suspended | Tidak diproses atau dilewati dengan log |

### 5.7 Izin dan cuti

| Skenario | Harapan |
| --- | --- |
| Ajukan izin 1 hari | Status `pending` |
| Ajukan 31 hari | 422 |
| Tanggal selesai sebelum mulai | 422 |
| Tanggal mulai kemarin | 422 |
| Tumpang tindih dengan pending | 422 |
| Tumpang tindih dengan approved | 422 |
| Cuti pada hari libur | 422 |
| Cuti pada hari kerja | Diterima |
| Setujui izin | Hari presensi `izin` dibuat untuk hari kerja |
| Setujui izin pada hari dengan absen masuk | Ditolak |
| Tolak izin tanpa catatan | 422 |
| Batalkan pending oleh anggota | Status `cancelled` |
| Batalkan approved oleh anggota | Ditolak |
| Batalkan approved oleh reviewer untuk besok | Hari tanpa kejadian dihapus |
| Batalkan approved untuk kemarin | Ditolak |

### 5.8 Tinjauan dan koreksi

| Skenario | Harapan |
| --- | --- |
| Tinjau kejadian ditolak | Status `pending` |
| Tinjau kejadian diterima | Ditolak |
| Dua tinjauan pending | Ditolak |
| Setujui tinjauan | Hari presensi dibuat, kejadian diterima |
| Setujui tinjauan saat sudah ada absen | Ditolak |
| Tolak tinjauan tanpa catatan | 422 |
| Reviewer di luar lingkup | 403 |
| Koreksi hari ini | Diterima |
| Koreksi 8 hari lalu | Ditolak |
| Dua koreksi pending | Ditolak |
| Setujui koreksi | Hari berubah, nilai asli tersimpan |
| Tolak koreksi | Hari tidak berubah |

### 5.9 Laporan dan CSV

| Skenario | Harapan |
| --- | --- |
| Bulan dengan 22 hari kerja | Ringkasan menghitung 22 |
| Hari libur | Tidak dihitung |
| Anggota dengan 3 terlambat | Ringkasan terlambat 3 |
| Anggota dengan izin 2 hari | Ringkasan izin 2 |
| Anggota tanpa presensi | Absen sesuai hari kerja |
| Filter divisi | Hanya anggota divisi itu |
| Filter lokasi | Hanya anggota lokasi itu |
| Reviewer | Hanya lingkup lokasinya |
| CSV | BOM, titik koma, CRLF, header benar |
| Nama berkas | `laporan-presensi-2026-10.csv` |
| Sel berisi titik koma | Di-escape dengan kutip |
| Angka ringkasan | Sama dengan jumlah baris rincian |

### 5.10 Selfie

| Skenario | Harapan |
| --- | --- |
| Unggah JPEG valid | File tersimpan di disk privat |
| EXIF | Tidak ada di file hasil |
| Ukuran 3000 px | Di-resize ke 1280 px |
| URL tanpa tanda tangan | 403 |
| URL kedaluwarsa | 403 |
| Anggota lain membuka | 403 |
| Reviewer luar lingkup | 403 |
| Platform admin | 403 |
| Retensi lewat | File dihapus, path kosong |
| Selfie dimatikan | Tidak ada file, presensi tetap |

## 6. Pengujian unit

### 6.1 GeofenceService

- Haversine menghitung jarak yang benar untuk koordinat yang diketahui.
- Titik tepat di pusat menghasilkan 0.
- Radius 50 m: 49 m cocok, 50 m cocok, 51 m tidak cocok.
- Buffer 50 m: 99 m masih diterima dengan tanda, 101 m ditolak.
- Beberapa lokasi: memilih yang terdekat yang cocok.
- Tidak ada lokasi aktif: mengembalikan `tanpa_lokasi`.
- Akurasi 100 m: tidak bertanda. 101 m: bertanda.
- Koordinat tidak valid: exception validasi.

### 6.2 TenantClock

- `today()` memakai zona tenant, bukan UTC.
- Pergantian hari di Jakarta terjadi pada 17.00 UTC.
- `combine()` menghasilkan UTC yang benar.
- `formatDate()` memakai nama bulan Indonesia.
- Zona tidak valid ditolak saat validasi pengaturan.

### 6.3 AttendanceCalculator

- Terlambat dihitung dari jam mulai, bukan akhir toleransi.
- Pulang cepat dihitung dari jam selesai.
- Pulang terlambat dihitung setelah 240 menit.
- Batas 24 jam.
- Tidak ada nilai negatif.

## 7. Pengujian kebocoran tenant

Tes ini wajib ada dan tidak boleh dilewati:

1. Buat tenant A dan B dengan anggota, lokasi, jadwal, hari presensi, izin,
   dan undangan.
2. Set context A.
3. Untuk setiap model tenant, panggil `Model::count()` dan pastikan hanya data A.
4. Coba akses URL detail data B sebagai pengguna A. Harapkan 404.
5. Coba ekspor laporan dengan `division_id` milik B. Harapkan 422 atau 404.
6. Coba pindah ke tenant B tanpa keanggotaan. Harapkan 403.
7. Coba buka selfie kejadian B. Harapkan 403.
8. Pastikan platform admin tidak dapat membuka `/app`.

Tes ini juga memeriksa bahwa `TenantContext` dibersihkan setelah request
pengujian, sehingga tes berikutnya tidak mewarisi tenant.

## 8. Pengujian waktu

- Selalu set waktu UTC. Contoh: 30 Oktober 2026 01.00 UTC sama dengan 08.00
  WIB.
- Jangan memakai `now()` tanpa `setTestNow()` pada tes yang bergantung waktu.
- Uji pergantian hari: 30 Oktober 2026 16.59 UTC masih 30 Oktober WIB, 17.00
  UTC sudah 31 Oktober WIB.
- Uji zona `Asia/Makassar` (UTC+8) dan `Asia/Jayapura` (UTC+9) minimal satu kali.
- Bersihkan `Carbon::setTestNow()` di `afterEach`.

## 9. Pengujian antarmuka

Pengujian otomatis tidak mencakup kamera dan GPS nyata. Matriks manual:

| Perangkat | Peramban | Yang diuji |
| --- | --- | --- |
| Android 12+ | Chrome | Izin lokasi, kamera, unggah selfie, mode gelap |
| iOS 16+ | Safari | Izin lokasi, kamera, unggah selfie, safe area |
| Desktop | Chrome | Keyboard, tabel lebar, peta |
| Desktop | Firefox | Form, modal, fokus |
| Layar 360 px | Chrome DevTools | Tidak ada scroll horizontal, target sentuh |
| Layar 1440 px | Chrome | Tata letak maksimal, tabel |

Skenario manual:

1. Tolak izin lokasi. Pastikan pesan jelas dan tombol coba lagi ada.
2. Matikan GPS. Pastikan pesan sesuai pengaturan wajib lokasi.
3. Absen di dalam radius, di buffer, dan di luar buffer.
4. Ambil selfie dengan kamera depan dan unggah dari galeri.
5. Cabut izin kamera di tengah alur. Pastikan tidak ada state menggantung.
6. Muat ulang halaman setelah absen. Pastikan status benar.
7. Buka mode gelap. Periksa kontras dan peta.
8. Gunakan keyboard saja untuk mengisi form izin.
9. Buka laporan satu bulan dengan 100 anggota. Periksa waktu muat.
10. Unduh CSV dan buka di Excel. Pastikan kolom dan encoding benar.

## 10. Pengujian performa

- Buat 100 anggota, 22 hari kerja, dan 2.200 hari presensi.
- Ukur laporan bulanan. Target p95 di bawah 2 detik di lingkungan staging.
- Ukur ekspor CSV. Target di bawah 5 detik untuk 2.200 baris.
- Ukur finalisasi 1.000 hari. Target di bawah 60 detik.
- Periksa jumlah query pada halaman daftar presensi. Target di bawah 15 query
  untuk 25 baris.

Alat: Laravel Debugbar hanya di lokal, atau `DB::listen()` pada tes khusus.
Jangan mengaktifkan Debugbar di produksi.

## 11. Kriteria lulus

- Semua tes otomatis lulus.
- Tidak ada tes yang dilewati tanpa alasan tertulis.
- `./vendor/bin/pint --test` lulus.
- `npm run build` lulus.
- Tidak ada galat JavaScript di konsol pada halaman utama.
- Matriks manual untuk rilis v1 sudah dijalankan dan dicatat.

## 12. Data uji

- Jangan memakai data pribadi nyata.
- Gunakan nama seperti "Ani Pratama", "Budi Santoso", dan domain `@hadir.test`.
- Foto uji dibuat dengan GD, bukan foto orang nyata.
- Koordinat uji memakai titik di Jakarta, misalnya `-6.200000, 106.816666`.
- Jangan menyimpan hasil selfie uji di repositori.

## 13. CI yang disarankan

```yaml
name: tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, gd, sqlite3, pdo_sqlite, fileinfo
      - run: composer install --no-interaction --prefer-dist
      - run: cp .env.example .env
      - run: php artisan key:generate
      - run: php artisan test
      - run: npm ci
      - run: npm run build
```

CI memakai SQLite. Pekerjaan terpisah dengan PostgreSQL disarankan sebelum rilis
produksi:

```yaml
  postgres:
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:16-alpine
        env:
          POSTGRES_PASSWORD: secret
          POSTGRES_DB: hadir_test
        ports: ['5432:5432']
        options: >-
          --health-cmd pg_isready --health-interval 10s
          --health-timeout 5s --health-retries 5
```

Jalankan `php artisan migrate --force` dan `php artisan test` dengan
`DB_CONNECTION=pgsql`.
