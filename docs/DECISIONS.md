# Keputusan Arsitektur Hadir.In

Versi 1.0. Catatan keputusan yang mengikat implementasi v1. Setiap perubahan
besar harus menambah ADR baru, bukan menulis ulang sejarah.

Format: nomor, judul, status, konteks, keputusan, konsekuensi.

## ADR-001: Monolit Laravel dengan Livewire

Status: diterima.

Konteks: tim kecil, satu produk web, kebutuhan form interaktif (kamera, peta,
pratinjau jadwal) tetapi tidak perlu API terpisah.

Keputusan: Laravel 12 sebagai monolit. Livewire 3 untuk halaman dan aksi
interaktif. Alpine.js hanya untuk perilaku browser kecil. Blade untuk komponen
tampilan. Tidak ada SPA, tidak ada API publik v1.

Konsekuensi:

- Satu basis kode, satu deployment, satu sesi.
- Logika bisnis dapat diuji tanpa browser.
- Halaman yang sangat interaktif tetap memerlukan JavaScript kecil.
- Bila kelak butuh aplikasi mobile, API perlu dirancang terpisah.

## ADR-002: Tailwind CSS 4 dan sistem komponen sendiri

Status: diterima.

Konteks: antarmuka harus terasa seperti buku catatan resmi, bukan dashboard SaaS
bawaan. Komponen siap pakai membawa gaya yang sulit dikendalikan.

Keputusan: Tailwind CSS 4 dengan token di `@theme`. Komponen Blade sendiri:
tombol, input, panel, status, tabel, modal, toast. Tidak memakai Filament,
Flowbite, DaisyUI, atau pustaka ikon seragam.

Konsekuensi:

- Token dan copy dapat dikendalikan penuh.
- Lebih banyak kode komponen awal.
- Konsistensi dijaga lewat `DESIGN.md` dan review.

## ADR-003: PostgreSQL di produksi, SQLite untuk pengujian lokal

Status: diterima.

Konteks: PostgreSQL 16 tersedia di produksi dan cocok untuk data relasional.
Mesin pengembangan lokal tidak memiliki ekstensi `pdo_pgsql`.

Keputusan: produksi memakai PostgreSQL 16. Pengujian otomatis dan pengembangan
lokal memakai SQLite. Migrasi harus portabel: tanpa enum PostgreSQL, tanpa
`jsonb` khusus, tanpa partial index, tanpa fungsi khusus PostgreSQL.

Konsekuensi:

- Tes cepat dan tidak membutuhkan server database.
- Perbedaan perilaku PostgreSQL belum tercakup tes lokal.
- Sebelum rilis, migrasi dan tes wajib dijalankan di staging PostgreSQL.
- Batasan dicatat di `OPERATIONS.md` dan `TESTING.md`.

## ADR-004: Satu database, skema bersama, global scope tenant

Status: diterima.

Konteks: multi-tenant untuk organisasi kecil. Isolasi per database terlalu mahal
untuk operasi dan migrasi. Row Level Security menambah kompleksitas yang sulit
diuji dengan SQLite.

Keputusan: satu database, kolom `tenant_id` pada tabel tenant, global scope
`BelongsToTenant`, dan `TenantContext` per request. Semua route model binding
melewati scope. Tidak memakai RLS.

Konsekuensi:

- Operasi dan migrasi sederhana.
- Kebocoran tenant menjadi risiko utama. Tes isolasi wajib.
- Query mentah harus menyertakan `tenant_id` secara eksplisit.
- Pekerjaan antrean harus menetapkan context tenant.

## ADR-005: Waktu UTC, tanggal kerja lokal

Status: diterima.

Konteks: tenant dapat berada di zona waktu berbeda. Penyimpanan waktu lokal
menyulitkan perbandingan dan laporan.

Keputusan: semua timestamp disimpan UTC. `tenants.timezone` menentukan zona
tampilan dan `work_date`. `work_date` adalah tanggal kalender lokal tenant.
Perhitungan jadwal memakai `TenantClock`.

Konsekuensi:

- Pergantian hari terjadi pada waktu UTC yang berbeda per tenant.
- Laporan mengelompokkan berdasarkan `work_date`, bukan tanggal UTC.
- Tes harus menetapkan waktu UTC eksplisit.

## ADR-006: Geofence haversine dengan buffer

Status: diterima.

Konteks: GPS ponsel tidak presisi. Menolak tepat di batas radius menimbulkan
keluhan yang sulit diselesaikan.

Keputusan: jarak dihitung dengan haversine. Radius lokasi adalah batas utama.
Buffer 50 meter (dapat diatur) menerima presensi dengan tanda `di_luar_radius`.
Akurasi di atas 100 meter diberi tanda `akurasi_rendah`. Bila tidak ada lokasi
aktif, presensi diterima dengan tanda `tanpa_lokasi` kecuali `require_location`
aktif.

Konsekuensi:

- Presensi di buffer tetap tercatat dan dapat ditinjau.
- Laporan menampilkan tanda, bukan menyembunyikannya.
- Koordinat dan jarak disimpan pada kejadian.

## ADR-007: Selfie privat dengan URL bertanda tangan

Status: diterima.

Konteks: selfie adalah data pribadi. URL publik permanen tidak dapat diterima.

Keputusan: file disimpan di disk privat. EXIF dihapus, gambar diperkecil ke
maksimal 1280 px, JPEG kualitas 80. Akses lewat URL bertanda tangan berlaku 5
menit dan policy. Retensi 90 hari; job harian menghapus file dan mengosongkan
path, tetapi mempertahankan catatan presensi.

Konsekuensi:

- Tidak ada akses langsung ke folder selfie.
- Tautan kedaluwarsa saat halaman dibuka terlalu lama.
- Backup selfie harus terpisah dan terenkripsi bila perlu.

## ADR-008: Riwayat presensi tidak dihapus

Status: diterima.

Konteks: koreksi dan sengketa membutuhkan jejak. Menghapus kejadian membuat
laporan tidak dapat dipertanggungjawabkan.

Keputusan: `attendance_events` tidak dihapus. Koreksi mengubah
`attendance_days` dan mencatat nilai asli serta nilai diminta di
`attendance_corrections`. Pembatalan izin yang disetujui boleh menghapus hari
presensi hanya bila belum ada kejadian dan tanggalnya masih hari ini atau nanti.

Konsekuensi:

- Data tumbuh, tetapi volume kecil.
- Laporan dapat menjelaskan perubahan.
- Tidak ada tombol hapus presensi di antarmuka.

## ADR-009: Hari presensi disimpan, status diturunkan saat dibutuhkan

Status: diterima.

Konteks: laporan membutuhkan hari kerja, hari libur, izin, dan ketidakhadiran.
Menghitung semuanya saat laporan dibuka membuat query rumit dan lambat.

Keputusan: `attendance_days` menyimpan baris untuk hari yang memiliki kejadian,
izin disetujui, atau hasil finalisasi. Hari kerja tanpa kejadian dan tanpa izin
dibuat sebagai `absen` oleh job finalisasi. Hari libur dan hari libur jadwal tidak
dibuatkan baris `absen`. Status `libur` adalah status turunan untuk tampilan, bukan
baris tersimpan dalam kondisi normal.

Konsekuensi:

- Laporan lebih sederhana.
- Finalisasi harus idempoten.
- Hari kerja yang belum lewat tidak boleh dianggap absen.

## ADR-010: Peran dan pemisahan platform admin

Status: diterima.

Konteks: pengelola instalasi berbeda dari pengelola organisasi. Pencampuran
peran berisiko membocorkan data tenant.

Keputusan: peran tenant: owner, admin, reviewer, member. Platform admin adalah
flag `users.is_platform_admin`, bukan peran tenant. Platform admin tidak memiliki
keanggotaan dan tidak dapat membuka `/app`. Akses platform lewat `/platform`
dengan middleware khusus. Platform admin hanya melihat metadata tenant.

Konsekuensi:

- Dua jalur masuk yang jelas.
- Platform admin tidak dapat membantu operasional tenant lewat antarmuka.
- Promosi platform admin lewat command, bukan web.

## ADR-011: Undangan dengan token sekali pakai

Status: diterima.

Konteks: menambahkan anggota tanpa mengirim kata sandi awal. Email harus
terkunci agar tautan tidak disalahgunakan.

Keputusan: token 32 byte acak, disimpan sebagai hash SHA-256, berlaku 7 hari,
sekali pakai. Email undangan terkunci. Penerima yang sudah punya akun harus masuk
dengan akun tersebut. Penerima baru membuat kata sandi saat menerima. Undangan
dapat dicabut. Satu email hanya boleh punya satu undangan tertunda per tenant.

Konsekuensi:

- Tautan aman bila bocor setelah dipakai.
- Email harus benar saat mengundang.
- Tidak ada undangan ulang otomatis; admin mencabut lalu mengundang lagi.

## ADR-012: Laporan CSV, bukan PDF

Status: diterima.

Konteks: pemilik usaha dan admin perlu mengolah data di spreadsheet. PDF
menambah pekerjaan tata letak dan sulit diolah.

Keputusan: ekspor CSV dengan UTF-8 BOM, pemisah titik koma, akhir baris CRLF,
header bahasa Indonesia, dan nama berkas `laporan-presensi-YYYY-MM.csv`. Tidak ada
ekspor PDF di v1.

Konsekuensi:

- Berkas langsung terbuka di Excel versi Indonesia.
- Sel yang mengandung titik koma dikutip.
- Tampilan cetak tidak disediakan.

## ADR-013: Antrean, cache, dan sesi di database

Status: diterima.

Konteks: VPS tunggal, volume rendah, tidak ingin menambah Redis pada v1.

Keputusan: `QUEUE_CONNECTION=database`, `CACHE_STORE=database`,
`SESSION_DRIVER=database`. Worker dijalankan supervisor. Untuk multi-server,
pindah ke Redis dan catat ADR baru.

Konsekuensi:

- Tidak ada layanan tambahan.
- Tabel antrean dan sesi tumbuh; bersihkan berkala.
- Throughput terbatas, tetapi cukup untuk ratusan anggota.

## ADR-014: Penyebaran VPS tanpa Docker di produksi

Status: diterima.

Konteks: tim kecil, satu server, ingin operasi yang mudah dipahami. Docker
menambah lapisan yang belum dibutuhkan.

Keputusan: Nginx, PHP-FPM, PostgreSQL, supervisor, cron, Certbot. Rilis berbasis
direktori dan symlink. Docker hanya untuk eksperimen lokal.

Konsekuensi:

- Perintah operasi standar Linux.
- Konsistensi lingkungan bergantung pada dokumentasi.
- Bila jumlah server bertambah, pertimbangkan container atau platform terkelola.

## ADR-015: Tanpa verifikasi email dan tanpa 2FA di v1

Status: diterima.

Konteks: pengguna dibuat lewat undangan atau pendaftaran organisasi. Verifikasi
email menambah gesekan tanpa manfaat besar untuk v1. 2FA belum menjadi kebutuhan
utama segmen ini.

Keputusan: tidak ada verifikasi email. Tidak ada 2FA. Kata sandi minimal 8
karakter, di-hash dengan bcrypt. Rate limit login 5 percobaan per menit.

Konsekuensi:

- Email tetap harus valid untuk menerima undangan.
- Keamanan akun bergantung pada kata sandi dan HTTPS.
- 2FA dapat ditambahkan kemudian tanpa mengubah model peran.

## ADR-016: Peta memakai tile OpenStreetMap

Status: diterima.

Konteks: pemilih lokasi membutuhkan peta. Layanan berbayar memerlukan kunci API
dan biaya.

Keputusan: Leaflet dengan tile OpenStreetMap. Atribusi ditampilkan. Peta hanya
untuk memilih dan memeriksa lokasi. Bila tile gagal dimuat, alamat dan koordinat
tetap tampil sebagai teks.

Konsekuensi:

- Tidak ada kunci API.
- Bergantung pada ketersediaan tile publik.
- Kebijakan penggunaan tile harus dipantau bila trafik besar.

## ADR-017: Font di-host sendiri

Status: diterima.

Konteks: Google Fonts menambah permintaan pihak ketiga dan dapat diblokir.

Keputusan: Plus Jakarta Sans untuk judul, Instrument Sans untuk teks, di-host
sendiri sebagai WOFF2. Fallback sistem disediakan.

Konsekuensi:

- Tidak ada permintaan ke Google.
- Ukuran aset awal bertambah.
- Lisensi font harus disertakan.

## ADR-018: ID bigint dan UUID percobaan

Status: diterima.

Konteks: idempotensi absen saat koneksi buruk. Pengguna dapat menekan tombol dua
kali atau melakukan percobaan ulang.

Keputusan: primary key bigint auto increment. Setiap percobaan presensi membawa
`attempt_uuid` yang dibuat klien. UUID unik di database. Percobaan dengan UUID
sama mengembalikan hasil yang sudah ada.

Konsekuensi:

- Tidak ada duplikasi karena retry.
- Klien wajib membuat UUID sebelum mengirim.
- UUID bukan pengenal publik utama; URL tetap memakai ID.

## ADR-019: Tanpa tabel audit log

Status: diterima.

Konteks: kebutuhan jejak v1 terbatas pada perubahan presensi dan tinjauan izin.

Keputusan: tidak ada tabel audit umum. Jejak perubahan presensi ada di
`attendance_events` dan `attendance_corrections`. Tinjauan izin menyimpan
`reviewed_by_id`, `reviewed_at`, dan catatan. Perubahan pengaturan tidak diaudit.

Konsekuensi:

- Skema lebih kecil.
- Perubahan pengaturan tidak dapat ditelusuri.
- Bila kebutuhan audit muncul, tambahkan tabel dan ADR baru.

## ADR-020: Hari libur tidak berulang

Status: diterima.

Konteks: hari libur Indonesia berubah setiap tahun. Mengulang tanggal yang sama
setiap tahun menghasilkan data salah.

Keputusan: tabel `holidays` menyimpan tanggal spesifik per tenant. Tidak ada
pengulangan otomatis. Admin menambah hari libur setiap tahun.

Konsekuensi:

- Data akurat.
- Admin perlu memperbarui awal tahun.
- Tidak ada impor kalender libur nasional di v1.

## ADR-021: Bahasa Indonesia saja

Status: diterima.

Konteks: pengguna utama di Indonesia. Menerjemahkan antarmuka menambah beban
copy dan pengujian.

Keputusan: antarmuka dan email berbahasa Indonesia. Kode, nama tabel, dan nama
kelas berbahasa Inggris. Tidak ada pemilih bahasa. Pesan bawaan Laravel
diterjemahkan di `lang/id` supaya tidak ada kunci mentah seperti
`validation.password.letters` yang tampil ke pengguna.

Konsekuensi:

- Copy dapat ditulis lebih matang.
- Format tanggal dan angka memakai kebiasaan Indonesia.
- Setiap kunci terjemahan baru dari kerangka kerja harus ditambahkan ke `lang/id`.
- Internasionalisasi dapat ditambahkan kemudian.

## ADR-022: Tidak ada penghapusan tenant

Status: diterima.

Konteks: penghapusan tenant menghapus data presensi dan selfie secara permanen.
Risiko salah hapus tinggi.

Keputusan: tenant hanya dapat ditangguhkan dan diaktifkan kembali. Tidak ada
tombol hapus. Penghapusan permanen dilakukan manual oleh operator dengan prosedur
tertulis di luar aplikasi.

Konsekuensi:

- Data tetap ada setelah organisasi berhenti.
- Status `suspended` memblokir akses anggota.
- Permintaan penghapusan harus ditangani sebagai prosedur operasi.

## ADR-023: Tidak ada lampiran izin

Status: diterima.

Konteks: surat dokter atau bukti izin sering berupa foto. Namun unggahan baru
menambah permukaan privasi dan penyimpanan.

Keputusan: izin, sakit, dan cuti tidak menerima lampiran di v1. Alasan ditulis
sebagai teks. Bila diperlukan, bukti diserahkan di luar aplikasi.

Konsekuensi:

- Alur lebih sederhana.
- Tidak ada penyimpanan dokumen medis.
- Kebutuhan lampiran dapat ditambahkan kemudian dengan kebijakan retensi sendiri.

## ADR-024: Satu pasangan absen per hari

Status: diterima.

Konteks: shift tunggal, tanpa lintas tengah malam. Absen berkali-kali membuat
perhitungan jam kerja ambigu.

Keputusan: satu absen masuk dan satu absen pulang per `work_date`. Percobaan
kedua ditolak. Shift lintas tengah malam tidak didukung v1.

Konsekuensi:

- Perhitungan sederhana.
- Tim shift malam memerlukan perubahan model dan ADR baru.
- Koreksi dapat memperbaiki pasangan yang salah tanpa menambah kejadian baru.
