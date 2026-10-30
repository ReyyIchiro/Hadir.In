# Operasi Hadir.In

Versi 1.0. Panduan menjalankan, menyebarkan, memantau, dan memulihkan Hadir.In.
Produksi diasumsikan satu VPS Linux dengan Nginx, PHP-FPM, PostgreSQL 16,
supervisor, dan cron.

## 1. Prasyarat server

### 1.1 Perangkat lunak

- Ubuntu 24.04 LTS atau Debian 12
- PHP 8.3 atau 8.4 dengan ekstensi `bcmath`, `ctype`, `curl`, `dom`,
  `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo_pgsql`, `pgsql`, `tokenizer`,
  `xml`, `zip`
- Composer 2
- PostgreSQL 16
- Nginx 1.24 atau lebih baru
- Node 20 LTS hanya untuk build aset
- Supervisor
- Certbot
- `pg_dump` dan `pg_restore`

Periksa ekstensi:

```bash
php -m | sort
php -r 'var_dump(extension_loaded("pdo_pgsql"));'
```

Bila `pdo_pgsql` belum ada:

```bash
sudo apt install php8.3-pgsql
sudo systemctl restart php8.3-fpm
```

### 1.2 Ukuran server minimum

- 2 vCPU
- 2 GB RAM
- 40 GB SSD
- 1 IPv4 publik

Untuk 500 anggota aktif harian, ukuran ini cukup. Pantau beban dan disk.

## 2. Struktur direktori

```
/var/www/hadir/
  current -> releases/2026-10-30-120000
  releases/
  shared/
    .env
    storage/
      app/private/selfies
      logs
  backup/
```

`current` adalah symlink ke rilis aktif. `shared` menyimpan `.env`, storage, dan
backup. Jangan menaruh `.env` di dalam repositori.

## 3. Variabel lingkungan

Contoh `.env` produksi:

```dotenv
APP_NAME=Hadir.In
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://hadir.example.id
APP_TIMEZONE=UTC
APP_LOCALE=id
APP_FALLBACK_LOCALE=id

LOG_CHANNEL=daily
LOG_LEVEL=warning
LOG_DAILY_DAYS=14

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hadir
DB_USERNAME=hadir
DB_PASSWORD=...

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.id
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@hadir.example.id
MAIL_FROM_NAME="Hadir.In"

SELFIE_DISK=private
SELFIE_RETENTION_DAYS=90
```

Catatan:

- `APP_TIMEZONE` wajib `UTC`. Zona tenant disimpan di database.
- `APP_DEBUG=false` di produksi.
- `SESSION_SECURE_COOKIE=true` hanya bila HTTPS aktif.
- Jangan memakai `MAIL_MAILER=log` di produksi kecuali tautan undangan disalin
  manual dan sudah disepakati.

## 4. Instalasi pertama

```bash
sudo adduser --disabled-password --gecos "" hadir
sudo mkdir -p /var/www/hadir/{releases,shared/storage/app/private/selfies,shared/storage/logs,backup}
sudo chown -R hadir:www-data /var/www/hadir
sudo chmod -R 775 /var/www/hadir/shared/storage
```

Sebagai pengguna `hadir`:

```bash
cd /var/www/hadir
git clone https://github.com/ReyyIchiro/Hadir.In.git releases/2026-10-30-120000
ln -s releases/2026-10-30-120000 current
cd current
composer install --no-dev --optimize-autoloader
cp /var/www/hadir/shared/.env .env
php artisan key:generate
```

Hubungkan storage bersama:

```bash
rm -rf storage
ln -s /var/www/hadir/shared/storage storage
```

Buat database dan pengguna:

```bash
sudo -u postgres psql <<'SQL'
create user hadir with password 'GANTI_DENGAN_SANDI_KUAT';
create database hadir owner hadir;
SQL
```

Migrasi:

```bash
php artisan migrate --force
```

Buat platform admin:

```bash
php artisan platform:promote admin@example.id
```

Build aset:

```bash
npm ci
npm run build
```

Optimasi:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Izin berkas:

```bash
sudo chown -R hadir:www-data /var/www/hadir/current
sudo find /var/www/hadir/current -type d -exec chmod 775 {} \;
sudo find /var/www/hadir/current -type f -exec chmod 664 {} \;
sudo chmod -R ug+rwX /var/www/hadir/shared/storage
```

## 5. Nginx

```nginx
server {
    listen 80;
    server_name hadir.example.id;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name hadir.example.id;

    root /var/www/hadir/current/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/hadir.example.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/hadir.example.id/privkey.pem;

    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    client_max_body_size 6M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_read_timeout 60s;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(?:css|js|jpg|jpeg|png|svg|woff2)$ {
        expires 30d;
        access_log off;
        try_files $uri =404;
    }
}
```

Aktifkan:

```bash
sudo ln -s /etc/nginx/sites-available/hadir /etc/nginx/sites-enabled/hadir
sudo nginx -t
sudo systemctl reload nginx
sudo certbot --nginx -d hadir.example.id
```

Catatan: tile peta dimuat dari `tile.openstreetmap.org`. Jangan memblokir
permintaan tersebut di firewall server. Pengguna yang memblokir tile tetap dapat
melihat alamat dan jarak sebagai teks.

## 6. PHP-FPM

```ini
; /etc/php/8.3/fpm/pool.d/hadir.conf
[hadir]
user = hadir
group = www-data
listen = /run/php/php8.3-fpm.sock
listen.owner = www-data
listen.group = www-data
pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 8
pm.max_requests = 500
```

Sesuaikan `pm.max_children` dengan RAM. Rumus kasar: RAM tersedia dibagi 80 MB
per proses.

```bash
sudo systemctl restart php8.3-fpm
```

## 7. Supervisor

`/etc/supervisor/conf.d/hadir-worker.conf`:

```ini
[program:hadir-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hadir/current/artisan queue:work --sleep=3 --tries=3 --timeout=60 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=hadir
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hadir/shared/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hadir-worker:*
```

## 8. Cron

```cron
* * * * * cd /var/www/hadir/current && php artisan schedule:run >> /dev/null 2>&1
```

Jadwal aplikasi:

- `attendance:finalize` setiap jam.
- `selfies:purge` setiap hari pukul 02.00 UTC.
- `model:prune` setiap hari pukul 03.00 UTC untuk undangan kedaluwarsa.

Pastikan hanya satu cron scheduler berjalan. Jangan menjalankan `schedule:run`
di dua server tanpa `onOneServer()` dan cache bersama.

## 9. Deployment rilis baru

```bash
cd /var/www/hadir
RELEASE=$(date +%Y-%m-%d-%H%M%S)
git clone --depth 1 https://github.com/ReyyIchiro/Hadir.In.git releases/$RELEASE
cd releases/$RELEASE
composer install --no-dev --optimize-autoloader
ln -s /var/www/hadir/shared/.env .env
rm -rf storage
ln -s /var/www/hadir/shared/storage storage
php artisan migrate --force
npm ci
npm run build
php artisan optimize
sudo chown -R hadir:www-data .
ln -sfn /var/www/hadir/releases/$RELEASE /var/www/hadir/current
sudo systemctl reload php8.3-fpm
sudo supervisorctl restart hadir-worker:*
```

Urutan penting:

1. Migrasi harus kompatibel mundur. Jangan menghapus kolom pada rilis yang sama
   dengan kode yang masih memakainya.
2. Build aset sebelum symlink berpindah.
3. Setelah symlink, bersihkan cache konfigurasi bila `.env` berubah.
4. Simpan dua rilis terakhir untuk rollback.

Rollback:

```bash
cd /var/www/hadir
ln -sfn releases/2026-10-29-090000 current
sudo systemctl reload php8.3-fpm
sudo supervisorctl restart hadir-worker:*
```

Rollback database hanya dari backup. Jangan menjalankan migrasi balik di produksi
tanpa rencana yang diuji.

## 10. Backup

### 10.1 Database

```bash
#!/usr/bin/env bash
set -euo pipefail
STAMP=$(date +%F-%H%M)
DIR=/var/www/hadir/backup
PGPASSWORD="$DB_PASSWORD" pg_dump -h 127.0.0.1 -U hadir -Fc hadir \
  > "$DIR/hadir-$STAMP.dump"
find "$DIR" -name 'hadir-*.dump' -mtime +14 -delete
```

Simpan di luar server, misalnya object storage atau server backup. Enkripsi bila
penyimpanan tidak tepercaya:

```bash
gpg --encrypt --recipient ops@example.id hadir-2026-10-30-0200.dump
```

### 10.2 Selfie

```bash
tar -czf /var/www/hadir/backup/selfies-$(date +%F).tar.gz \
  -C /var/www/hadir/shared/storage/app/private selfies
find /var/www/hadir/backup -name 'selfies-*.tar.gz' -mtime +28 -delete
```

### 10.3 Uji pemulihan

Setiap kuartal:

```bash
createdb -U postgres hadir_restore_test
pg_restore -U postgres -d hadir_restore_test hadir-2026-10-30-0200.dump
psql -U postgres -d hadir_restore_test -c 'select count(*) from tenants;'
dropdb -U postgres hadir_restore_test
```

Catat hasilnya di catatan operasi. Backup yang belum diuji bukan backup.

## 11. Pemantauan

Minimum yang dipantau:

- `GET /health` setiap 1 menit. Harapkan 200.
- Ruang disk. Peringatan di 80 persen.
- Beban CPU dan RAM.
- Jumlah `failed_jobs`.
- Panjang antrean `jobs`.
- Kesalahan 5xx di log Nginx.
- Sertifikat TLS kedaluwarsa. Peringatan 14 hari sebelum.
- Backup terakhir. Peringatan bila lebih dari 26 jam.

Contoh cek cepat:

```bash
curl -fsS https://hadir.example.id/health
php artisan queue:monitor default:100
php artisan about
tail -n 100 storage/logs/laravel-$(date +%F).log
```

Log rotasi ditangani `LOG_CHANNEL=daily` dan logrotate untuk Nginx.

## 12. Pemecahan masalah

### 12.1 Halaman 500 setelah deploy

1. Periksa `storage/logs/laravel-*.log`.
2. Pastikan `.env` ada dan `APP_KEY` terisi.
3. Jalankan `php artisan config:clear` lalu `php artisan config:cache`.
4. Periksa izin `storage` dan `bootstrap/cache`.
5. Periksa versi PHP dan ekstensi `pdo_pgsql`.

### 12.2 Presensi ditolak padahal di kantor

1. Periksa koordinat lokasi dan radius di `/app/lokasi`.
2. Minta pengguna mengirim tangkapan layar jarak dan akurasi.
3. Periksa `attendance_events` terakhir: `latitude`, `longitude`, `accuracy_meters`,
   `distance_meters`, `reason`.
4. Bila akurasi rendah, minta pengguna mencoba di area terbuka.
5. Bila jarak salah, perbaiki koordinat lokasi. Jangan mengubah radius hanya untuk
   satu orang tanpa persetujuan.
6. Bila valid, minta pengguna mengajukan tinjauan.

### 12.3 Selfie tidak tampil

1. Periksa `selfie_path` pada kejadian.
2. Pastikan file ada di `shared/storage/app/private/selfies/...`.
3. Pastikan URL belum kedaluwarsa. URL hanya berlaku 5 menit.
4. Periksa policy: pengguna harus pemilik, admin, atau peninjau dalam lingkup.
5. Jangan mengubah disk menjadi publik untuk memperbaiki masalah.

### 12.4 Finalisasi tidak berjalan

1. Periksa cron: `crontab -l`.
2. Jalankan manual: `php artisan attendance:finalize -v`.
3. Periksa zona waktu tenant dan jam selesai.
4. Periksa apakah hari sudah memiliki izin disetujui.
5. Periksa log command.

### 12.5 Email undangan tidak terkirim

1. Periksa konfigurasi SMTP.
2. Periksa `failed_jobs`.
3. Jalankan ulang: `php artisan queue:retry all`.
4. Tautan undangan tetap dapat disalin dari `/app/anggota/undangan`.
5. Jangan menonaktifkan antrean untuk mengakali masalah.

### 12.6 Sesi terus keluar

1. Periksa `SESSION_DRIVER=database` dan tabel `sessions`.
2. Periksa `SESSION_SECURE_COOKIE` bila HTTPS belum aktif.
3. Periksa `APP_URL` dan domain cookie.
4. Periksa ruang disk. Sesi database gagal ditulis bila disk penuh.

### 12.7 Peta tidak muncul

1. Periksa koneksi ke `tile.openstreetmap.org`.
2. Periksa CSP bila ada.
3. Peta tetap harus menampilkan alamat dan jarak sebagai teks.
4. Jangan menambahkan kunci API pihak ketiga tanpa ADR.

## 13. Pemeliharaan rutin

Harian:

- Periksa health check dan backup.
- Periksa `failed_jobs`.

Mingguan:

- Periksa ruang disk dan log galat.
- Periksa antrean tinjauan yang menua.

Bulanan:

- Periksa pembaruan keamanan PHP dan dependensi.
- Periksa sertifikat TLS.
- Tinjau log akses untuk pola aneh.

Kuartalan:

- Uji pemulihan backup.
- Tinjau retensi selfie.
- Tinjau akun yang tidak aktif.

## 14. Pembaruan dependensi

```bash
composer outdated
composer update --with-all-dependencies
php artisan test
npm outdated
npm update
npm run build
```

Jangan memperbarui dependensi mayor di produksi tanpa pengujian staging. Catat
perubahan penting di `DECISIONS.md`.

## 15. Keamanan operasional

- Jangan memakai akun `root` untuk menjalankan aplikasi.
- Nonaktifkan login kata sandi SSH. Gunakan kunci.
- Batasi akses PostgreSQL ke localhost.
- Jangan membuka port 5432 ke internet.
- Gunakan firewall: hanya 22, 80, 443.
- Ganti kata sandi database secara berkala.
- Jangan menyimpan `.env` di repositori atau backup yang tidak terenkripsi.
- Cabut akses platform admin yang tidak lagi bertugas.
- Periksa `users.is_platform_admin` secara berkala.
- Jangan mengirim data presensi lewat email atau chat.
- Hapus akses anggota yang berhenti bekerja dengan menonaktifkan keanggotaan.

## 16. Lingkungan lokal

Mesin pengembangan proyek ini memakai Arch Linux dengan PHP 8.5 dari paket
resmi. Ekstensi `pdo_pgsql` tidak ikut dalam paket `php`, melainkan paket
terpisah `php-pgsql`, sehingga awalnya tidak tersedia. Sekarang ekstensi itu
sudah dipasang tanpa akses root dengan cara berikut.

### 16.1 Memasang pdo_pgsql tanpa root

Paket `php-pgsql` dan `postgresql-libs` diunduh lalu diekstrak ke direktori
pengguna, tidak ke sistem:

```bash
mkdir -p ~/.local/lib/php-pgsql/{modules,lib}
cd /tmp
curl -sSL -O "$(pacman -Sp php-pgsql | head -1)"
curl -sSL -O "$(pacman -Sp postgresql-libs | head -1)"
mkdir -p root
for f in *.pkg.tar.zst; do bsdtar -xf "$f" -C root; done
cp root/usr/lib/php/modules/pdo_pgsql.so ~/.local/lib/php-pgsql/modules/
cp -P root/usr/lib/libpq.so* root/usr/lib/libpgtypes.so* ~/.local/lib/php-pgsql/lib/
printf 'extension=%s/.local/lib/php-pgsql/modules/pdo_pgsql.so\n' "$HOME" \
  > ~/.local/lib/php-pgsql/pdo_pgsql.ini
```

`~/.local/bin/php` adalah pembungkus tipis yang menyetel `LD_LIBRARY_PATH` dan
`PHP_INI_SCAN_DIR` lalu menjalankan `/usr/bin/php`. Direktori itu ada di depan
`PATH`, jadi `php` otomatis memuat ekstensi tanpa variabel lingkungan manual:

```sh
#!/bin/sh
PHP_PGSQL_HOME="$HOME/.local/lib/php-pgsql"
export LD_LIBRARY_PATH="$PHP_PGSQL_HOME/lib"
export PHP_INI_SCAN_DIR="$PHP_PGSQL_HOME:/etc/php/conf.d"
exec /usr/bin/php "$@"
```

`/etc/php/conf.d` tetap disertakan agar ekstensi bawaan (`gd`, `pdo_sqlite`,
`mbstring`) tidak hilang. Bila `pdo_pgsql.so` hilang setelah peningkatan PHP,
PHP hanya mencetak peringatan dan tetap berjalan; perbaiki dengan mengulang
langkah di atas memakai versi paket yang cocok dengan `php -v`.

### 16.2 Menjalankan aplikasi pada PostgreSQL

```bash
DB_CONNECTION=pgsql DB_HOST=127.0.0.1 DB_PORT=55432 \
DB_DATABASE=hadir DB_USERNAME=postgres DB_PASSWORD=secret \
php artisan migrate:fresh --seed
```

Tanpa variabel di atas, `.env` memakai SQLite dan aplikasi tetap jalan. Untuk
memakai PostgreSQL secara permanen, ganti blok `DB_*` di `.env` mengikuti
contoh yang sudah dikomentari di `.env.example`.

### 16.3 Menjalankan pengujian pada PostgreSQL

`phpunit.xml` menyetel `DB_CONNECTION=sqlite` agar `php artisan test` cepat dan
tidak butuh server. Variabel lingkungan dari shell menimpa nilai itu, sehingga
seluruh tes dapat dijalankan pada PostgreSQL:

```bash
DB_CONNECTION=pgsql DB_HOST=127.0.0.1 DB_PORT=55432 \
DB_DATABASE=hadir_test DB_USERNAME=postgres DB_PASSWORD=secret \
php artisan test
```

Hasil pada mesin ini: 167 tes lulus, 360 asersi, PostgreSQL 16.15. Migrasi,
seed, login, halaman presensi, dan ekspor CSV juga sudah diverifikasi pada
PostgreSQL.

Catatan penting: `php artisan test` tanpa variabel di atas **tetap memakai
SQLite**. Jangan menganggap keluaran hijau membuktikan kompatibilitas
PostgreSQL. Jalankan perintah dengan `DB_CONNECTION=pgsql` sebelum rilis.
CI melakukan hal yang sama lewat layanan PostgreSQL di `.github/workflows/ci.yml`.

### 16.4 Hal yang tetap hanya bisa diuji manual

Kamera, GPS, dan izin browser tidak tercakup pengujian otomatis pada platform
apa pun. Jalankan matriks manual di `TESTING.md` bagian 7 pada perangkat nyata.

## 17. Runbook insiden

### 17.1 Situs tidak dapat diakses

1. Periksa status server dan jaringan.
2. Periksa `systemctl status nginx php8.3-fpm postgresql supervisor`.
3. Periksa disk dengan `df -h`.
4. Periksa log Nginx dan Laravel.
5. Bila karena rilis baru, rollback symlink.
6. Bila karena database, pulihkan dari backup terakhir.
7. Catat waktu, penyebab, dan tindakan.

### 17.2 Data terlihat lintas organisasi

1. Anggap sebagai insiden keamanan serius.
2. Nonaktifkan akses aplikasi sementara dengan mode pemeliharaan:
   `php artisan down --secret="..."`.
3. Identifikasi query atau rute yang bocor.
4. Perbaiki, tambah tes regresi.
5. Beri tahu pihak yang berwenang sesuai kebijakan.
6. Jangan menghapus log.

### 17.3 Selfie bocor

1. Nonaktifkan URL yang bocor bila memungkinkan.
2. Periksa log akses untuk pola pengambilan.
3. Rotasi `APP_KEY` hanya bila diperlukan dan pahami dampaknya pada sesi.
4. Perbaiki policy atau tanda tangan URL.
5. Catat dan laporkan.

## 18. Checklist go-live

- [ ] Domain dan TLS aktif.
- [ ] `APP_DEBUG=false`.
- [ ] `APP_ENV=production`.
- [ ] `APP_KEY` dibuat dan disimpan aman.
- [ ] Database PostgreSQL dibuat dan dimigrasi.
- [ ] Platform admin dibuat.
- [ ] SMTP diuji.
- [ ] Worker supervisor berjalan.
- [ ] Cron scheduler berjalan.
- [ ] Backup harian berjalan dan diuji.
- [ ] Health check dipantau.
- [ ] Log rotate aktif.
- [ ] Firewall membatasi port.
- [ ] Tes otomatis lulus di staging PostgreSQL.
- [ ] Matriks manual kamera dan GPS lulus.
- [ ] Halaman privasi dan ketentuan ditinjau.
- [ ] Tidak ada data demo di produksi.
- [ ] Tidak ada akun dengan kata sandi bawaan.
- [ ] Rencana rollback diketahui.
