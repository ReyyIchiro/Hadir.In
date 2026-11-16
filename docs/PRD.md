# PRD Hadir.In

Versi 1.0. Tanggal 30 Oktober 2026. Status: disetujui untuk implementasi v1.

## 1. Ringkasan

Hadir.In adalah aplikasi presensi berbasis web untuk kantor dan tim lapangan di
Indonesia. Aplikasi mencatat absen masuk dan pulang dengan lokasi GPS dan selfie,
mengelola jadwal kerja, izin, cuti, hari libur, serta menghasilkan laporan bulanan
yang dapat diekspor ke CSV.

Produk ini ditujukan untuk organisasi kecil dan menengah yang membutuhkan bukti
kehadiran yang rapi tanpa sistem HRIS besar. Satu instalasi dapat melayani banyak
organisasi (multi-tenant). Setiap organisasi memiliki data, anggota, lokasi, dan
pengaturan sendiri.

## 2. Masalah

1. Presensi manual di buku, spreadsheet, atau grup chat sulit direkap dan mudah
   hilang.
2. Aplikasi presensi besar terlalu mahal dan terlalu banyak fitur untuk kebutuhan
   harian.
3. Tim lapangan sering bekerja di lokasi berbeda, sehingga absen berbasis kantor
   tetap tidak cukup.
4. Pemilik usaha perlu melihat siapa yang hadir, terlambat, izin, atau absen
   tanpa menunggu akhir bulan.
5. Data kehadiran dan foto pribadi perlu disimpan dengan jelas: siapa yang boleh
   melihat, berapa lama, dan untuk apa.

## 3. Pengguna dan peran

### 3.1 Platform admin

Pengelola instalasi. Tidak menjadi anggota organisasi mana pun. Dapat melihat
daftar organisasi, status, paket, dan jumlah anggota. Tidak dapat melihat data
presensi, selfie, jadwal, izin, atau detail anggota.

### 3.2 Pemilik organisasi (owner)

Membuat organisasi saat mendaftar. Memiliki akses penuh ke organisasi, termasuk
pengaturan, anggota, lokasi, divisi, jadwal, hari libur, tinjauan, dan laporan.
Dapat mengundang admin, peninjau, dan anggota. Tidak dapat dihapus dari organisasi
selama organisasi ada. Dapat masuk saat organisasi masih menunggu aktivasi, tetapi
hanya melihat halaman status.

### 3.3 Admin organisasi

Membantu operasi harian. Dapat mengelola anggota (kecuali mengubah atau menghapus
pemilik), lokasi, divisi, jadwal, hari libur, pengaturan, tinjauan, dan laporan.
Dapat mengundang peninjau dan anggota. Tidak dapat mengundang admin lain.

### 3.4 Peninjau (reviewer)

Menyetujui atau menolak pengajuan presensi, koreksi, izin, dan cuti dalam
lingkup lokasi utamanya. Dapat melihat presensi dan laporan pada lingkup tersebut.
Tidak dapat mengubah pengaturan organisasi atau mengelola anggota.

### 3.5 Anggota

Mencatat absen masuk dan pulang, melihat riwayat sendiri, mengajukan izin atau
cuti, mengajukan koreksi, dan melihat jadwal sendiri. Tidak dapat melihat data
anggota lain.

## 4. Tujuan dan non-tujuan

### 4.1 Tujuan v1

- Menyediakan satu alur presensi harian yang cepat dipakai di ponsel.
- Memberi bukti lokasi dan selfie yang dapat ditinjau kembali.
- Menghasilkan laporan bulanan yang dapat diunduh dan dibuka di Excel.
- Menjaga batas data antar organisasi dan antar peran.
- Menyediakan antarmuka berbahasa Indonesia yang tenang dan mudah dipelajari.

### 4.2 Non-tujuan v1

- Payroll, slip gaji, BPJS, PPh 21, dan perhitungan upah.
- Manajemen proyek, tugas, atau shift swap.
- Rekrutmen, onboarding dokumen, dan penilaian kinerja.
- Aplikasi mobile native.
- Absensi offline.
- Pengenalan wajah otomatis atau liveness detection.
- Notifikasi push, WhatsApp, atau SMS.
- Integrasi mesin fingerprint.
- Multi-bahasa. Antarmuka hanya bahasa Indonesia.
- Kustomisasi merek white-label.
- API publik.

## 5. Ruang lingkup v1

### 5.1 Organisasi dan akses

- Pendaftaran organisasi baru dengan status menunggu aktivasi.
- Aktivasi, penangguhan, dan pengaktifan kembali oleh platform admin.
- Undangan anggota melalui tautan sekali pakai.
- Peran pemilik, admin, peninjau, dan anggota.
- Satu pengguna dapat menjadi anggota beberapa organisasi dan berpindah
  organisasi aktif.
- Pengaturan organisasi: nama, zona waktu, aturan presensi, dan retensi selfie.

### 5.2 Data operasional

- Lokasi kerja dengan koordinat, radius, dan status aktif.
- Divisi untuk pengelompokan anggota dan laporan.
- Jadwal per anggota per tanggal.
- Penerapan jadwal massal berdasarkan anggota, divisi, atau lokasi.
- Hari libur organisasi.

### 5.3 Presensi

- Absen masuk dan pulang dengan validasi jadwal, radius, akurasi GPS, dan selfie.
- Pencatatan percobaan yang ditolak tanpa menghapus riwayat.
- Status hadir, terlambat, izin, sakit, cuti, dan absen.
- Tanda kondisi: tanpa lokasi, akurasi rendah, di luar radius, diterima manual,
  final otomatis, dan sejenisnya.
- Finalisasi harian otomatis.
- Pengajuan tinjauan untuk percobaan yang ditolak.
- Koreksi presensi oleh anggota dengan persetujuan peninjau atau admin.

### 5.4 Izin dan cuti

- Pengajuan izin, sakit, dan cuti.
- Persetujuan atau penolakan oleh peninjau atau admin.
- Pembatalan oleh anggota untuk pengajuan yang masih menunggu.
- Pembatalan izin yang sudah disetujui oleh peninjau atau admin, dengan batas
  tanggal yang dijelaskan di aturan bisnis.

### 5.5 Laporan

- Ringkasan bulanan per anggota.
- Rincian harian.
- Ekspor CSV dengan format yang konsisten untuk Excel Indonesia.
- Filter divisi dan lokasi sesuai peran.

### 5.6 Halaman publik

- Beranda, cara kerja, untuk siapa, pertanyaan, privasi, dan ketentuan.
- Masuk, daftar, lupa kata sandi, atur ulang kata sandi, dan penerimaan undangan.

## 6. Model konsep

| Konsep | Arti |
| --- | --- |
| Organisasi | Tenant. Wadah data, anggota, dan pengaturan. |
| Keanggotaan | Hubungan antara pengguna dan organisasi, menyimpan peran. |
| Lokasi | Titik kerja dengan koordinat dan radius. |
| Divisi | Kelompok kerja untuk filter dan laporan. |
| Jadwal | Rencana kerja satu anggota pada satu tanggal. |
| Hari libur | Tanggal tidak bekerja untuk seluruh organisasi. |
| Hari presensi | Satu baris per anggota per tanggal kerja yang memiliki kejadian atau status. |
| Kejadian presensi | Catatan percobaan absen masuk atau pulang, termasuk yang ditolak. |
| Pengajuan tinjauan | Permintaan agar percobaan presensi yang ditolak diperiksa ulang. |
| Koreksi | Permintaan perubahan data hari presensi dengan alasan. |
| Izin | Pengajuan izin, sakit, atau cuti untuk satu rentang tanggal. |

## 7. Alur utama

### 7.1 Pendaftaran dan aktivasi organisasi

1. Pengunjung membuka `/daftar`.
2. Mengisi nama, email, kata sandi, nama organisasi, dan zona waktu.
3. Sistem membuat pengguna, organisasi berstatus `pending`, keanggotaan pemilik,
   dan pengaturan bawaan.
4. Pengguna otomatis masuk dan diarahkan ke `/status`.
5. Halaman status menjelaskan bahwa organisasi menunggu aktivasi platform admin.
6. Platform admin membuka `/platform/tenant`, memeriksa organisasi, lalu
   mengaktifkannya.
7. Setelah aktif, pemilik dapat masuk ke aplikasi. Anggota baru dapat masuk
   setelah organisasi aktif.

Organisasi yang ditangguhkan tidak dapat diakses anggota. Pemilik melihat halaman
status penangguhan. Tidak ada penghapusan organisasi sendiri di v1.

Formulir pendaftaran memakai honeypot: satu kolom tersembunyi bernama
`website` yang wajib kosong. Formulir yang mengisinya ditolak sebagai upaya
bot, di samping pembatasan laju lima percobaan per menit per email dan IP.

### 7.2 Undangan anggota

1. Admin atau pemilik membuka `/app/anggota/undang`.
2. Mengisi email, peran, lokasi utama, dan divisi.
3. Sistem membuat token 32 byte, menyimpan hash-nya, dan mengirim tautan
   `/undangan/{token}` melalui email. Tautan juga ditampilkan kepada pengundang
   agar dapat disalin bila email belum dikonfigurasi.
4. Token berlaku 7 hari dan hanya dapat dipakai sekali.
5. Penerima membuka tautan. Email terkunci dan tidak dapat diubah.
6. Bila pengguna belum ada, penerima mengisi nama dan kata sandi. Bila sudah ada,
   penerima harus masuk dengan akun beremail sama.
7. Keanggotaan dibuat. Bila organisasi aktif, penerima langsung masuk. Bila belum
   aktif, penerima diarahkan ke halaman masuk dengan pesan bahwa organisasi belum
   aktif.

### 7.3 Absen masuk

1. Anggota membuka `/app/presensi/masuk`.
2. Aplikasi meminta izin lokasi dan menampilkan peta, alamat, jarak ke lokasi
   terdekat, dan akurasi GPS.
3. Anggota mengambil selfie melalui kamera perangkat.
4. Anggota menekan "Absen masuk".
5. Sistem memvalidasi jadwal, waktu, lokasi, akurasi, selfie, dan duplikasi.
6. Bila diterima, sistem membuat hari presensi dan kejadian masuk, lalu
   menampilkan waktu, status, dan lokasi.
7. Bila ditolak, sistem menyimpan kejadian ditolak, menampilkan alasan yang jelas,
   dan menawarkan "Ajukan tinjauan" bila relevan.

### 7.4 Absen pulang

1. Anggota membuka `/app/presensi/pulang`.
2. Sistem menampilkan waktu masuk dan durasi kerja.
3. Anggota mengambil selfie dan menekan "Absen pulang".
4. Sistem menolak bila belum ada absen masuk, sudah absen pulang, atau sudah lebih
   dari 24 jam sejak masuk.
5. Bila diterima, waktu pulang disimpan. Keterlambatan pulang dan pulang cepat
   dicatat sebagai tanda, bukan alasan penolakan.

### 7.5 Izin dan cuti

1. Anggota membuka `/app/izin/baru`.
2. Memilih jenis, tanggal mulai, tanggal selesai, dan alasan.
3. Sistem memvalidasi rentang, tumpang tindih, dan aturan cuti.
4. Pengajuan berstatus `pending` dan masuk antrean peninjau.
5. Peninjau atau admin menyetujui atau menolak dengan catatan.
6. Bila disetujui, sistem membuat hari presensi bertipe izin, sakit, atau cuti
   pada hari kerja yang terjadwal dan bukan hari libur.
7. Hari yang sudah memiliki absen masuk tidak dapat disetujui sebagai izin.

### 7.6 Koreksi

1. Anggota membuka detail hari presensi di `/app/presensi/{attendanceDay}`.
2. Menekan "Ajukan koreksi", mengisi waktu masuk atau pulang yang benar dan alasan.
3. Sistem menyimpan nilai asli dan nilai yang diminta.
4. Peninjau atau admin menyetujui atau menolak.
5. Bila disetujui, hari presensi diperbarui dan nilai asli tetap tersimpan di
   catatan koreksi. Kejadian presensi tidak dihapus.

### 7.7 Laporan bulanan

1. Admin, pemilik, atau peninjau membuka `/app/laporan`.
2. Memilih bulan dan filter divisi atau lokasi.
3. Sistem menampilkan ringkasan: hari dijadwalkan, hadir, terlambat, izin dan
   cuti, absen, serta jumlah yang perlu ditinjau.
4. Tabel rincian menampilkan satu baris per anggota per tanggal. Hari yang belum
   berjalan tidak ditampilkan. Hari berjalan yang belum melewati batas finalisasi
   tampil sebagai "Belum absen" dan dihitung terpisah.
5. Tombol "Ekspor CSV" mengunduh berkas dengan format yang dijelaskan di bagian 8.

## 8. Aturan bisnis

### 8.1 Waktu dan tanggal

- BR-01. Semua waktu disimpan dalam UTC. Tanggal kerja (`work_date`) adalah
  tanggal lokal pada zona waktu organisasi.
- BR-02. Zona waktu organisasi dipilih dari `Asia/Jakarta`, `Asia/Makassar`, atau
  `Asia/Jayapura`. Bawaan `Asia/Jakarta`.
- BR-03. Jam kerja disimpan sebagai jam lokal organisasi. Tidak ada shift yang
  melewati tengah malam di v1. Jam selesai harus lebih besar dari jam mulai.
- BR-04. Absen masuk paling awal 60 menit sebelum jam mulai. Percobaan lebih awal
  ditolak dengan alasan "Terlalu awal".
- BR-05. Absen masuk setelah jam selesai ditolak dengan alasan "Jadwal sudah
  berakhir" dan dapat diajukan tinjauan.
- BR-06. Terlambat bila waktu masuk lebih dari jam mulai ditambah toleransi 10
  menit. Menit keterlambatan dihitung sejak jam mulai, bukan sejak akhir toleransi.
- BR-07. Absen pulang setelah jam selesai ditambah 240 menit dicatat dengan tanda
  "Pulang terlambat", tetapi tetap diterima selama belum lewat 24 jam sejak masuk.
- BR-08. Absen pulang lebih dari 10 menit sebelum jam selesai dicatat dengan tanda
  "Pulang cepat".
- BR-09. Absen pulang ditolak bila lebih dari 24 jam sejak absen masuk.
- BR-10. Satu anggota hanya boleh memiliki satu absen masuk dan satu absen pulang
  per tanggal kerja.

### 8.2 Jadwal dan hari libur

- BR-11. Jadwal bersifat per anggota per tanggal. Satu anggota hanya memiliki satu
  jadwal per tanggal.
- BR-12. Jadwal dapat berupa hari kerja dengan jam mulai, jam selesai, dan istirahat
  opsional, atau hari libur (`is_day_off`).
- BR-13. Jam istirahat harus berada di dalam rentang jam kerja. Bila salah satu
  jam istirahat diisi, keduanya wajib diisi.
- BR-14. Penerapan massal membuat jadwal untuk setiap tanggal dalam rentang yang
  dipilih, pada hari dalam minggu yang dipilih, untuk anggota yang dipilih.
  Rentang maksimal 92 hari. Mode "lewati yang sudah ada" tidak menimpa jadwal
  yang sudah ada. Mode "timpa" mengganti jadwal pada tanggal tersebut.
- BR-15. Hari libur adalah tanggal tidak bekerja untuk seluruh organisasi. Hari
  libur tidak berulang otomatis.
- BR-16. Hari libur tidak dihitung sebagai hari yang diharapkan hadir. Absen pada
  hari libur tidak dihitung sebagai absen.
- BR-17. Absen masuk pada hari libur diperbolehkan dan dicatat dengan tanda
  "Hari libur", status hadir atau terlambat sesuai waktu.
- BR-18. Bila organisasi mengizinkan presensi tanpa jadwal, absen pada hari tanpa
  jadwal diterima dengan tanda "Tanpa jadwal". Bila tidak diizinkan, ditolak.
- BR-19. Jadwal hari libur (`is_day_off`) tidak diharapkan untuk presensi. Absen
  pada hari itu mengikuti aturan BR-18.

### 8.3 Lokasi dan geofence

- BR-20. Jarak dihitung dengan rumus haversine dalam meter, dibulatkan ke bawah.
- BR-21. Lokasi yang cocok adalah lokasi aktif terdekat yang jaraknya kurang dari
  atau sama dengan radius.
- BR-22. Bila tidak ada lokasi dalam radius tetapi jarak masih dalam radius ditambah
  buffer 50 meter, presensi diterima dengan tanda "Di luar radius".
- BR-23. Bila jarak melebihi radius ditambah buffer, presensi ditolak dengan alasan
  "Di luar radius" dan dapat diajukan tinjauan.
- BR-24. Akurasi GPS lebih dari 100 meter dicatat dengan tanda "Akurasi rendah".
  Presensi tetap diterima bila jarak memenuhi aturan radius.
- BR-25. Bila organisasi tidak memiliki lokasi aktif, presensi diterima dengan
  tanda "Tanpa lokasi" dan tanpa validasi jarak.
- BR-26. Bila lokasi wajib dan GPS tidak tersedia, presensi ditolak dengan alasan
  "Lokasi tidak tersedia". Bila lokasi tidak wajib, presensi diterima dengan tanda
  "Tanpa lokasi".
- BR-27. Waktu tunggu GPS 15 detik. Setelah itu pengguna dapat mencoba lagi.
- BR-28. Lokasi yang dipakai pada kejadian adalah lokasi terdekat yang cocok.
  Bila tidak ada yang cocok, lokasi kosong.

### 8.4 Selfie

- BR-29. Selfie wajib secara bawaan. Organisasi dapat mematikannya di pengaturan.
- BR-30. Format yang diterima: JPEG, PNG, WebP. Ukuran maksimal 5 MB.
- BR-31. Selfie diproses ulang: EXIF dihapus, ukuran maksimal 1280 piksel pada sisi
  terpanjang, kualitas JPEG 80.
- BR-32. Selfie disimpan di disk privat, bukan di direktori publik.
- BR-33. Selfie ditampilkan melalui URL bertanda tangan yang berlaku 5 menit.
- BR-34. Selfie disimpan 90 hari secara bawaan. Setelah itu berkas dihapus dan
  path dikosongkan, tetapi catatan presensi tetap ada.
- BR-35. Platform admin tidak dapat melihat selfie.

### 8.5 Status dan finalisasi

- BR-36. Status hari presensi: `hadir`, `terlambat`, `izin`, `sakit`, `cuti`,
  `absen`. Status `libur`, `belum_pulang`, `belum_absen`, dan `terjadwal` adalah
  tampilan turunan, bukan nilai yang disimpan untuk alur normal.
- BR-37. Hari dengan absen masuk tetapi belum absen pulang ditampilkan "Belum
  pulang" selama belum difinalisasi.
- BR-38. Finalisasi berjalan setiap jam. Hari difinalisasi setelah jam selesai
  ditambah 240 menit.
- BR-39. Bila tidak ada absen masuk pada hari kerja terjadwal, bukan hari libur,
  dan tidak ada izin disetujui, sistem membuat hari presensi berstatus `absen`
  saat finalisasi. Sebelum batas finalisasi terlewati, hari itu ditampilkan
  "Belum absen" dan tidak dihitung sebagai absen pada ringkasan mana pun.
- BR-40. Bila ada absen masuk tetapi tidak ada absen pulang, hari tetap dianggap
  hadir atau terlambat dengan tanda "Final otomatis" dan "Tanpa pulang".
- BR-41. Saat anggota absen masuk pada hari baru, hari sebelumnya yang masih
  terbuka difinalisasi lebih dulu dengan tanda "Final otomatis".
- BR-42. Hari yang sudah difinalisasi tidak berubah karena finalisasi berikutnya.
  Perubahan hanya melalui koreksi yang disetujui.

### 8.6 Izin dan cuti

- BR-43. Jenis pengajuan: `izin`, `sakit`, `cuti`.
- BR-44. Tanggal mulai tidak boleh sebelum hari ini. Tanggal selesai tidak boleh
  sebelum tanggal mulai. Rentang maksimal 30 hari.
- BR-45. Pengajuan tidak boleh tumpang tindih dengan pengajuan lain yang masih
  menunggu atau disetujui untuk anggota yang sama.
- BR-46. Cuti hanya boleh diajukan untuk hari kerja terjadwal yang bukan hari
  libur. Izin dan sakit boleh diajukan pada tanggal apa pun, tetapi hanya membuat
  hari presensi pada hari kerja terjadwal yang bukan hari libur.
- BR-47. Izin yang disetujui memblokir absen masuk pada tanggal tersebut.
- BR-48. Persetujuan ditolak bila salah satu tanggal dalam rentang sudah memiliki
  absen masuk.
- BR-49. Anggota dapat membatalkan pengajuan yang masih menunggu.
- BR-50. Peninjau atau admin dapat membatalkan izin yang sudah disetujui hanya
  untuk tanggal mulai hari ini atau di masa depan. Hari presensi otomatis yang
  belum memiliki kejadian dihapus. Izin pada masa lalu tidak dibatalkan; gunakan
  koreksi.
- BR-51. Satu hari presensi hanya boleh memiliki satu status izin, sakit, atau cuti.

### 8.7 Tinjauan dan koreksi

- BR-52. Pengajuan tinjauan hanya untuk kejadian presensi yang ditolak. Satu
  kejadian hanya boleh memiliki satu pengajuan yang masih menunggu.
- BR-53. Peninjau hanya dapat meninjau kejadian yang lokasinya atau lokasi utama
  anggotanya sama dengan lokasi utama peninjau. Admin dan pemilik dapat meninjau
  semua.
- BR-54. Persetujuan tinjauan membuat atau memperbarui hari presensi bila hari
  tersebut belum memiliki absen masuk. Bila sudah ada, persetujuan ditolak.
- BR-55. Koreksi dapat diajukan anggota untuk hari presensi miliknya dalam 7 hari
  terakhir. Satu hari hanya boleh memiliki satu koreksi yang masih menunggu.
- BR-56. Koreksi menyimpan nilai asli dan nilai yang diminta dalam JSON. Nilai
  asli tidak boleh hilang.
- BR-57. Koreksi yang disetujui memperbarui hari presensi, menandai hari sebagai
  terkoreksi, dan tidak menghapus kejadian presensi.
- BR-58. Penolakan tinjauan atau koreksi wajib memiliki catatan.

### 8.8 Peran dan lingkup

- BR-59. Pemilik tidak dapat dihapus atau diturunkan perannya selama organisasi ada.
- BR-60. Admin tidak dapat mengubah atau menghapus pemilik, dan tidak dapat
  mengundang admin lain.
- BR-61. Peninjau wajib memiliki lokasi utama. Lokasi utama wajib diisi saat peran
  peninjau diberikan.
- BR-62. Lingkup peninjau: hari presensi yang lokasi absen masuknya sama dengan
  lokasi utama peninjau, atau anggota yang lokasi utamanya sama dengan lokasi
  utama peninjau. Izin mengikuti lokasi utama anggota. Laporan memakai lingkup
  yang sama.

  Untuk laporan, lingkup diterapkan pada anggota, bukan pada tiap hari, karena
  baris laporan adalah ringkasan per anggota. Peninjau melihat seluruh hari
  anggota yang lokasi utamanya sama dengan lokasi utamanya, termasuk hari ketika
  anggota tersebut absen di lokasi lain. Menyaring per hari akan memecah satu
  anggota menjadi beberapa baris dan membuat angka ringkasan menyesatkan.
- BR-63. Platform admin tidak boleh memiliki keanggotaan di organisasi mana pun.
- BR-64. Satu pengguna dapat memiliki beberapa keanggotaan. Sesi menyimpan
  organisasi aktif. Perpindahan organisasi hanya ke organisasi tempat pengguna
  menjadi anggota dan hanya bila organisasi aktif, kecuali pemilik yang menunggu
  aktivasi.

### 8.9 Undangan

- BR-65. Token undangan 32 byte acak, dikirim sebagai teks biasa di tautan, dan
  disimpan sebagai hash SHA-256.
- BR-66. Token berlaku 7 hari, sekali pakai, dan dapat dicabut.
- BR-67. Email undangan terkunci. Penerima tidak dapat mengganti email.
- BR-68. Bila email sudah terdaftar, penerima harus masuk dengan akun tersebut.
- BR-69. Bila email belum terdaftar, penerima membuat nama dan kata sandi.
- BR-70. Satu email hanya boleh memiliki satu undangan menunggu per organisasi.
  Undangan lama harus dicabut atau kedaluwarsa sebelum undangan baru dibuat.

### 8.10 Laporan dan ekspor

- BR-71. Laporan bulanan menghitung hari yang dijadwalkan, bukan jumlah hari
  kalender.
- BR-72. Hari libur dan hari libur jadwal tidak dihitung sebagai hari yang
  diharapkan hadir.
- BR-73. Kehadiran yang masih menunggu tinjauan ditampilkan dengan tanda "Perlu
  ditinjau" dan dihitung sebagai hadir pada ringkasan sementara.
- BR-74. Kejadian yang ditolak tidak dihitung hadir.
- BR-75. Ekspor CSV memakai UTF-8 dengan BOM, pemisah titik koma, akhir baris CRLF,
  dan header bahasa Indonesia.
- BR-76. Nama berkas: `laporan-presensi-YYYY-MM.csv`.
- BR-77. Kolom CSV: `Nama;Email;Divisi;Lokasi;Tanggal;Hari;Jadwal;Masuk;Pulang;Status;Keterangan`.
- BR-78. Tanggal di CSV memakai format `YYYY-MM-DD`, jam memakai `HH:MM`, dan
  status memakai label bahasa Indonesia.
- BR-79. Ekspor mengikuti filter dan lingkup yang sama dengan tampilan laporan.

### 8.11 Keamanan dan data

- BR-80. Setiap query data organisasi wajib dibatasi oleh organisasi aktif.
- BR-81. Kata sandi minimal 8 karakter, mengandung huruf dan angka. Kata sandi
  di-hash dengan algoritma bawaan Laravel.
- BR-82. Login dibatasi 5 percobaan per menit per kombinasi email dan IP.
- BR-83. Sesi memakai database. Sesi diperbarui saat login dan dihapus saat keluar.
- BR-84. Semua form memakai CSRF. Semua akses data memakai policy.
- BR-85. Tidak ada tabel audit terpisah di v1. Jejak perubahan ada pada kejadian
  presensi, koreksi, dan status pengajuan.
- BR-86. Organisasi tidak dapat menghapus dirinya sendiri. Platform admin hanya
  dapat menangguhkan atau mengaktifkan.
- BR-87. Data presensi tidak dihapus. Retensi hanya menghapus berkas selfie.
- BR-88. Akurasi GPS perangkat lebih dari 500 meter menolak presensi karena
  koordinat tidak dapat dipercaya. Percobaan tetap dicatat dengan alasan yang
  jelas. Di bawah 500 meter, akurasi lebih dari 100 meter tetap diterima dengan
  tanda sesuai BR-24.

## 9. Kebutuhan fungsional

### 9.1 Akun dan organisasi

- FR-01. Pengguna dapat mendaftar dengan nama, email, kata sandi, nama organisasi,
  dan zona waktu.
- FR-02. Sistem membuat organisasi berstatus menunggu aktivasi dan pengguna
  sebagai pemilik.
- FR-03. Pengguna dapat masuk dan keluar.
- FR-04. Pengguna dapat meminta tautan atur ulang kata sandi.
- FR-05. Pengguna dapat mengatur ulang kata sandi dengan token yang berlaku.
- FR-06. Pemilik dapat melihat status organisasi yang menunggu atau ditangguhkan.
- FR-07. Platform admin dapat melihat daftar organisasi dan statusnya.
- FR-08. Platform admin dapat mengaktifkan, menangguhkan, dan mengaktifkan kembali
  organisasi.
- FR-09. Pengguna dengan beberapa keanggotaan dapat berpindah organisasi aktif.

### 9.2 Anggota dan undangan

- FR-10. Admin dan pemilik dapat mengundang anggota dengan email, peran, lokasi
  utama, dan divisi.
- FR-11. Sistem mengirim email undangan dan menampilkan tautan undangan.
- FR-12. Penerima dapat menerima undangan dengan membuat akun atau masuk.
- FR-13. Admin dan pemilik dapat melihat daftar undangan menunggu dan mencabutnya.
- FR-14. Admin dan pemilik dapat melihat daftar anggota.
- FR-15. Admin dan pemilik dapat mengubah peran, lokasi utama, divisi, dan status
  aktif anggota sesuai batas peran.
- FR-16. Pemilik dapat mengangkat atau menurunkan admin. Admin tidak dapat
  mengubah admin atau pemilik.

### 9.3 Lokasi dan divisi

- FR-17. Admin dan pemilik dapat membuat, mengubah, menonaktifkan, dan melihat
  lokasi.
- FR-18. Form lokasi menampilkan peta dan dapat memilih titik dengan klik.
- FR-19. Admin dan pemilik dapat membuat, mengubah, dan menonaktifkan divisi.
- FR-20. Lokasi dan divisi yang masih dipakai tidak dapat dihapus permanen.
  Nonaktifkan sebagai gantinya.

### 9.4 Jadwal dan hari libur

- FR-21. Admin dan pemilik dapat melihat jadwal mingguan per anggota.
- FR-22. Admin dan pemilik dapat mengubah jadwal satu tanggal.
- FR-23. Admin dan pemilik dapat menerapkan jadwal massal dengan pratinjau
  jumlah jadwal yang akan dibuat atau ditimpa.
- FR-24. Admin dan pemilik dapat menambah dan menghapus hari libur.
- FR-25. Anggota dapat melihat jadwalnya sendiri.

### 9.5 Presensi

- FR-26. Anggota dapat melihat kartu presensi hari ini di beranda.
- FR-27. Anggota dapat absen masuk dengan lokasi dan selfie.
- FR-28. Anggota dapat absen pulang dengan lokasi dan selfie.
- FR-29. Sistem menolak presensi di luar aturan dan menyimpan alasan penolakan.
- FR-30. Sistem menampilkan jarak, akurasi, dan lokasi terdekat sebelum absen.
- FR-31. Anggota dapat melihat riwayat presensi bulanan.
- FR-32. Anggota dapat melihat detail hari presensi, termasuk selfie dan tanda.
- FR-33. Anggota dapat mengajukan tinjauan untuk kejadian yang ditolak.
- FR-34. Anggota dapat mengajukan koreksi untuk hari presensi dalam 7 hari.
- FR-35. Admin, pemilik, dan peninjau dapat melihat daftar presensi harian.
- FR-36. Admin, pemilik, dan peninjau dapat membuka detail presensi.
- FR-37. Peninjau dapat menyetujui atau menolak pengajuan tinjauan.
- FR-38. Peninjau dapat menyetujui atau menolak koreksi.
- FR-39. Finalisasi otomatis berjalan setiap jam.
- FR-40. Anggota dapat melihat status "Belum pulang" dan "Final otomatis".

### 9.6 Izin dan cuti

- FR-41. Anggota dapat mengajukan izin, sakit, atau cuti.
- FR-42. Anggota dapat melihat daftar dan detail pengajuannya.
- FR-43. Anggota dapat membatalkan pengajuan yang masih menunggu.
- FR-44. Peninjau dan admin dapat melihat antrean pengajuan.
- FR-45. Peninjau dan admin dapat menyetujui atau menolak dengan catatan.
- FR-46. Peninjau dan admin dapat membatalkan izin yang disetujui untuk tanggal
  hari ini atau masa depan.
- FR-47. Persetujuan membuat hari presensi izin, sakit, atau cuti pada hari kerja
  yang berlaku.

### 9.7 Laporan

- FR-48. Admin, pemilik, dan peninjau dapat memilih bulan dan filter.
- FR-49. Sistem menampilkan ringkasan dan rincian harian.
- FR-50. Sistem mengekspor CSV sesuai filter dan lingkup.
- FR-51. Laporan menampilkan tanda untuk data yang perlu ditinjau.

### 9.8 Pengaturan dan tampilan

- FR-52. Admin dan pemilik dapat mengubah nama organisasi dan zona waktu.
- FR-53. Admin dan pemilik dapat mengubah aturan presensi, geofence, dan retensi.
- FR-54. Pengguna dapat mengubah nama, email, dan kata sandi sendiri.
- FR-55. Pengguna dapat memilih tema terang, gelap, atau mengikuti sistem.
- FR-56. Pengguna dapat melihat dan memilih organisasi aktif.

## 10. Kebutuhan non-fungsional

- NFR-01. Halaman aplikasi yang sudah masuk dimuat dalam waktu wajar pada koneksi
  4G. Target p95 di bawah 2 detik untuk data satu bulan dengan 100 anggota.
- NFR-02. Aplikasi dapat dipakai pada layar 360 px tanpa scroll horizontal pada
  konten utama.
- NFR-03. Semua tindakan penting dapat dijangkau dengan keyboard.
- NFR-04. Kontras teks minimal 4.5:1 dan kontras komponen minimal 3:1.
- NFR-05. Tidak ada data organisasi yang terlihat oleh organisasi lain, termasuk
  melalui URL langsung, pencarian, atau ekspor.
- NFR-06. Selfie tidak dapat diakses tanpa sesi dan tanda tangan URL yang sah.
- NFR-07. Kesalahan validasi menjelaskan apa yang salah dan cara memperbaikinya.
- NFR-08. Aplikasi tetap dapat menampilkan data meski JavaScript gagal dimuat
  untuk halaman baca utama. Form penting memberi umpan balik galat yang jelas.
- NFR-09. Tidak ada emoji, em dash, atau klaim palsu di antarmuka.
- NFR-10. Waktu respons finalisasi untuk 1.000 hari presensi di bawah 60 detik
  pada VPS 2 vCPU.

## 11. Daftar layar

Daftar lengkap dan tata letak ada di `DESIGN.md` bagian 8. Ringkasnya:

- Publik: beranda, cara kerja, untuk siapa, pertanyaan, privasi, ketentuan,
  masuk, daftar, lupa kata sandi, atur ulang kata sandi, undangan, status.
- Anggota: beranda, absen masuk, absen pulang, riwayat, detail hari, jadwal,
  izin, form izin, profil, tampilan.
- Admin dan peninjau: ringkasan, presensi, detail presensi, tinjauan, jadwal,
  terapkan massal, hari libur, izin, laporan, anggota, undangan, lokasi, divisi,
  pengaturan.
- Platform: masuk, daftar organisasi, detail organisasi, ubah status.

## 12. Istilah dan copy

| Istilah | Arti | Jangan pakai |
| --- | --- | --- |
| Organisasi | Tenant | Perusahaan, akun |
| Anggota | Pengguna dalam organisasi | Karyawan, user |
| Pemilik | Peran tertinggi organisasi | Owner, super admin |
| Admin | Pengelola organisasi | Administrator |
| Peninjau | Pemberi persetujuan | Reviewer, approver |
| Presensi | Bagian dan konsep kehadiran | Absensi, attendance |
| Absen masuk | Tindakan mencatat kedatangan | Check-in, clock in |
| Absen pulang | Tindakan mencatat kepulangan | Check-out, clock out |
| Hari presensi | Satu baris kehadiran per tanggal | Record |
| Tinjauan | Pemeriksaan ulang percobaan ditolak | Review, banding |
| Koreksi | Perubahan data dengan alasan | Edit, revisi |
| Izin | Ketidakhadiran terencana | Permohonan |
| Sakit | Ketidakhadiran karena sakit | Sakit izin |
| Cuti | Ketidakhadiran berbayar | Libur |

Nada copy: tenang, langsung, tidak birokratis. Sapa dengan "Anda". Tombol memakai
kata kerja. Tidak ada tanda seru. Tidak ada emoji. Tidak ada em dash.

## 13. Kriteria penerimaan v1

### 13.1 Organisasi dan akses

- [x] Pendaftaran membuat organisasi menunggu aktivasi, pemilik, dan pengaturan
      bawaan dalam satu transaksi.
- [x] Pendaftaran yang mengisi honeypot `website` ditolak tanpa membuat data.
- [x] Pemilik dapat masuk saat menunggu dan hanya melihat halaman status.
- [x] Anggota tidak dapat masuk saat organisasi belum aktif.
- [x] Organisasi yang ditangguhkan memblokir akses anggota.
- [x] Platform admin dapat mengaktifkan dan menangguhkan organisasi.
- [x] Platform admin tidak dapat membuka data presensi atau selfie.
- [x] Pengguna dengan dua keanggotaan dapat berpindah organisasi tanpa melihat
      data organisasi lain.

### 13.2 Undangan

- [x] Undangan kedaluwarsa setelah 7 hari.
- [x] Token hanya dapat dipakai sekali.
- [x] Email undangan tidak dapat diubah.
- [x] Undangan ke email terdaftar mewajibkan login.
- [x] Undangan ke email baru membuat akun dan keanggotaan.
- [x] Undangan yang dicabut tidak dapat dipakai.
- [x] Peran yang diundang tidak dapat melebihi wewenang pengundang.

### 13.3 Presensi

- [x] Absen masuk 61 menit sebelum jadwal ditolak.
- [x] Absen masuk setelah jam selesai ditolak.
- [x] Absen masuk 11 menit setelah jam mulai tercatat terlambat dengan menit yang
      benar.
- [x] Absen di luar radius tetapi di dalam buffer diterima dengan tanda.
- [x] Absen di luar radius dan buffer ditolak.
- [x] Akurasi di atas 100 meter diberi tanda.
- [x] Organisasi tanpa lokasi menerima absen dengan tanda tanpa lokasi.
- [x] Absen ganda ditolak.
- [x] Absen pulang tanpa absen masuk ditolak.
- [x] Absen pulang setelah 24 jam ditolak.
- [x] Absen pada hari libur diterima dengan tanda.
- [x] Absen pada hari tanpa jadwal mengikuti pengaturan.
- [x] Izin yang disetujui memblokir absen masuk.
- [x] Selfie tersimpan di disk privat dan hanya dapat diakses lewat URL bertanda
      tangan.
- [x] Finalisasi membuat absen untuk hari kerja yang terlewat.
- [x] Finalisasi menutup hari tanpa pulang dengan tanda.
- [x] Absen masuk hari baru menutup hari sebelumnya yang terbuka.

### 13.4 Izin dan koreksi

- [x] Pengajuan lebih dari 30 hari ditolak.
- [x] Pengajuan yang tumpang tindih ditolak.
- [x] Cuti pada hari libur ditolak.
- [x] Persetujuan izin pada hari yang sudah ada absen masuk ditolak.
- [x] Koreksi menyimpan nilai asli.
- [x] Koreksi yang disetujui mengubah hari presensi tanpa menghapus kejadian.
- [x] Peninjau hanya melihat data dalam lokasi utamanya.

### 13.5 Laporan

- [x] Laporan menghitung hari terjadwal, bukan hari kalender.
- [x] Hari libur tidak dihitung sebagai absen.
- [x] CSV memiliki BOM, pemisah titik koma, dan header yang benar.
- [x] Nama berkas sesuai format.
- [x] Filter dan lingkup ekspor sama dengan tampilan.
- [x] Angka ringkasan cocok dengan baris rincian.

### 13.6 Antarmuka

- [x] Tidak ada emoji, em dash, atau klaim palsu.
- [x] Token warna sesuai `DESIGN.md`.
- [x] Tampil baik di 360 px, 768 px, 1024 px, dan 1440 px.
- [x] Mode gelap berfungsi dan kontras lulus.
- [x] Keadaan kosong, memuat, dan galat tersedia.
- [x] Fokus keyboard terlihat.
- [x] Peta tetap terang di mode gelap.

## 14. Risiko dan asumsi

| Risiko | Dampak | Mitigasi |
| --- | --- | --- |
| GPS tidak akurat di dalam gedung | Absen ditolak atau ditandai | Buffer 50 m, tanda akurasi rendah, batas 500 m, tinjauan |
| Kamera perangkat tidak tersedia | Anggota tidak dapat absen | Pesan jelas, selfie dapat dimatikan admin |
| Email belum dikonfigurasi | Undangan tidak terkirim | Tautan undangan ditampilkan untuk disalin |
| Zona waktu salah | Tanggal kerja bergeser | Zona waktu eksplisit saat daftar dan di pengaturan |
| Selfie disalahgunakan | Privasi | Disk privat, URL 5 menit, retensi 90 hari |
| Data lintas organisasi bocor | Kepercayaan hilang | Scope tenant, policy, pengujian kebocoran |
| Server VPS tunggal gagal | Layanan berhenti | Backup harian, runbook pemulihan |

Asumsi:

- Pengguna memiliki ponsel dengan kamera dan GPS.
- Koneksi internet tersedia saat absen.
- Organisasi memiliki minimal satu lokasi atau bersedia memakai mode tanpa lokasi.
- Platform admin adalah pihak tepercaya yang mengelola status organisasi.
- Jam kerja tidak melewati tengah malam pada v1.

## 15. Metrik keberhasilan

Metrik diukur dari penggunaan nyata, bukan target karangan:

- Persentase hari kerja dengan presensi tercatat.
- Jumlah koreksi per 100 hari presensi.
- Waktu rata-rata dari buka halaman absen sampai presensi tersimpan.
- Jumlah pengajuan tinjauan yang selesai dalam 1 hari kerja.
- Jumlah organisasi aktif dan anggota aktif per minggu.
- Jumlah kesalahan yang dilaporkan per 1.000 presensi.

Tidak ada metrik yang ditampilkan di halaman publik sebelum ada data nyata.
