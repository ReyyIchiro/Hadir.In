# DESIGN.md

Spesifikasi visual kanonik Hadir.In. Semua layar, komponen, dan copy antarmuka
mengikuti dokumen ini. Bila implementasi berbeda dari dokumen ini, implementasi
yang diperbaiki, kecuali dokumen ini diperbarui lebih dulu.

## 1. Arah desain

Hadir.In adalah buku absen resmi yang dipindahkan ke layar. Desainnya tenang,
utilitarian, dan sedikit arsiparis: kertas hangat, garis tipis, angka tabular,
dan satu warna stempel untuk tindakan penting.

Kata kunci: buku besar, stempel, kertas, disiplin, lokal, jelas.

Yang dihindari: gradien dekoratif, kaca buram, kartu bertumpuk dengan bayangan
besar, ilustrasi 3D, ikon garis seragam dari pustaka besar, ungu SaaS, dan
dashboard penuh kotak berwarna.

Prinsip:

1. Kejelasan mengalahkan kepadatan. Satu layar punya satu pekerjaan utama.
2. Garis mengalahkan kotak. Pemisah tipis lebih sering dipakai daripada kartu.
3. Warna status hanya untuk status. Warna stempel hanya untuk tindakan utama.
4. Angka rata kanan dan memakai angka tabular. Tanggal dan jam selalu jelas.
5. Tidak ada dekorasi yang tidak membawa informasi.

## 2. Token warna

Nilai berikut adalah nilai kanonik. Implementasi di `resources/css/app.css`
memakai nama variabel yang sama.

### 2.1 Mode terang

| Token | Nilai | Pemakaian |
| --- | --- | --- |
| `--color-paper` | `#F6F1E7` | Latar halaman |
| `--color-surface` | `#FFFDF8` | Panel, tabel, form |
| `--color-card` | `#FFFDFB` | Kartu ringan di atas surface |
| `--color-text` | `#1F1D1A` | Teks utama |
| `--color-muted` | `#6B6459` | Teks sekunder, label, keterangan |
| `--color-secondary` | `#5C564B` | Teks pendukung yang perlu lebih tegas |
| `--color-divider` | `#E4DCCB` | Garis pemisah dan border |
| `--color-stamp` | `#A63A2B` | Tindakan utama, tanda penting |
| `--color-stamp-deep` | `#8F3023` | Hover dan pressed |
| `--color-stamp-tint` | `#F0DBD3` | Latar lembut untuk stempel |
| `--color-stamp-light` | `#E7C9C0` | Border lembut bernuansa stempel |
| `--color-ink` | `#2B2823` | Panel gelap, teks di panel gelap |
| `--color-tint` | `#F2EDE2` | Latar baris terpilih, header tabel |
| `--color-highlight` | `#D9B36A` | Sorotan netral, bukan status |
| `--color-data` | `#5B9BD5` | Aksen data, garis grafik |
| `--color-data-tint` | `#DCE9F2` | Latar data |
| `--color-present` | `#3F6B50` | Status hadir |
| `--color-late` | `#8A651C` | Status terlambat |
| `--color-leave` | `#3C6278` | Status izin dan cuti |
| `--color-sick` | `#6B4B72` | Status sakit |
| `--color-absent` | `#8C3B33` | Status absen |
| `--color-focus` | `#2B2823` | Cincin fokus |

Kontras terukur di mode terang:

| Pasangan | Rasio | Nilai minimum |
| --- | --- | --- |
| teks pada paper | 14.94:1 | 4.5:1 |
| muted pada paper | 5.19:1 | 4.5:1 |
| stempel pada paper | 5.72:1 | 4.5:1 |
| surface pada stempel | 6.33:1 | 4.5:1 |
| hadir pada paper | 5.44:1 | 4.5:1 |
| terlambat pada paper | 4.71:1 | 4.5:1 |
| izin pada paper | 5.81:1 | 4.5:1 |
| sakit pada paper | 6.49:1 | 4.5:1 |
| absen pada paper | 6.69:1 | 4.5:1 |
| data pada paper | 2.63:1 | dekoratif saja |

`--color-data` tidak boleh dipakai untuk teks kecil. Untuk teks, gunakan
`--color-leave` atau `--color-secondary`.

### 2.2 Mode gelap

Mode gelap aktif lewat `data-theme="dark"` pada elemen `html`. Pilihan pengguna
disimpan di `localStorage` dengan kunci `hadir-theme`. Tanpa pilihan, gunakan
preferensi sistem.

| Token | Nilai | Catatan |
| --- | --- | --- |
| `--color-paper` | `#171512` | Latar halaman |
| `--color-surface` | `#201D19` | Panel |
| `--color-card` | `#24201B` | Kartu |
| `--color-text` | `#EDE6D8` | Teks utama |
| `--color-muted` | `#A79D8E` | Teks sekunder |
| `--color-secondary` | `#C4B9A7` | Teks pendukung |
| `--color-divider` | `#35302A` | Garis |
| `--color-stamp` | `#D96A58` | Tindakan utama |
| `--color-stamp-deep` | `#E07A68` | Hover |
| `--color-stamp-tint` | `#3A2620` | Latar lembut |
| `--color-stamp-light` | `#5A352D` | Border lembut |
| `--color-ink` | `#0F0E0C` | Panel lebih gelap |
| `--color-tint` | `#2A2621` | Baris terpilih |
| `--color-highlight` | `#D9B36A` | Sorotan |
| `--color-data` | `#8FB3C9` | Aksen data |
| `--color-data-tint` | `#25323A` | Latar data |
| `--color-present` | `#7FBF9A` | Hadir |
| `--color-late` | `#D9B36A` | Terlambat |
| `--color-leave` | `#8FB3C9` | Izin dan cuti |
| `--color-sick` | `#B99BC0` | Sakit |
| `--color-absent` | `#D99A9A` | Absen |
| `--color-focus` | `#EDE6D8` | Fokus |

Kontras mode gelap:

| Pasangan | Rasio |
| --- | --- |
| teks pada paper | 14.67:1 |
| teks pada surface | 13.52:1 |
| muted pada paper | 6.82:1 |
| stempel pada paper | 5.34:1 |
| stempel pada surface | 4.92:1 |
| stempel pada card | 4.74:1 |
| hadir pada surface | 7.85:1 |
| terlambat pada surface | 8.48:1 |
| izin pada surface | 7.56:1 |
| sakit pada surface | 6.80:1 |
| absen pada surface | 7.22:1 |

Teks di atas tombol stempel memakai `#FFFDF8` pada mode terang dan `#171512`
pada mode gelap. Rasio keduanya di atas 4.5:1.

### 2.3 Aturan pemakaian warna

- Stempel hanya untuk satu tindakan utama per layar dan untuk tanda status
  penting seperti "Perlu ditinjau".
- Warna status dipakai sebagai teks atau titik kecil, bukan latar kartu penuh.
  Latar status memakai `color-mix` 12 persen dengan surface.
- Jangan memakai warna stempel untuk tautan biasa. Tautan memakai teks utama
  dengan garis bawah saat hover.
- Jangan memakai lebih dari satu warna aksen di satu komponen.
- Peta tetap terang di kedua mode. Jangan menerapkan filter gelap ke tile peta.

## 3. Tipografi

Dua keluarga huruf:

- **Plus Jakarta Sans** untuk judul, angka besar, dan label stempel.
  Bobot 500, 600, 700. Dimuat dari server sendiri, bukan CDN.
- **Instrument Sans** untuk teks isi, tabel, form, dan tombol.
  Bobot 400, 500, 600. Dimuat dari server sendiri.

Fallback: `system-ui, -apple-system, "Segoe UI", sans-serif`. Untuk angka
tabular di tabel dan laporan: `font-variant-numeric: tabular-nums`.

Skala:

| Nama | Ukuran | Tinggi baris | Pemakaian |
| --- | --- | --- | --- |
| `text-12` | 12 px | 16 px | Label kecil, keterangan |
| `text-14` | 14 px | 20 px | Teks tabel, form, tombol |
| `text-16` | 16 px | 24 px | Teks isi |
| `text-20` | 20 px | 28 px | Subjudul, judul kartu |
| `text-28` | 28 px | 34 px | Judul halaman |
| `text-40` | 40 px | 46 px | Judul bagian pemasaran |
| `text-64` | 64 px | 66 px | Hero pemasaran, angka utama |

Aturan:

- Huruf besar untuk judul memakai `letter-spacing: -0.02em`; teks kecil memakai
  `letter-spacing: 0`.
- Label semua kapital hanya untuk eyebrow dan header tabel. Ukuran 12 px,
  `letter-spacing: 0.08em`, bobot 600.
- Panjang baris teks isi maksimal 68 karakter.
- Jangan memakai lebih dari tiga bobot dalam satu layar.

## 4. Spasi, radius, border, bayangan

Spasi mengikuti kelipatan 4: 4, 8, 12, 16, 24, 32, 48, 64, 96.

- Padding panel: 24 px di desktop, 16 px di ponsel.
- Jarak antarbagian: 32 px. Jarak antarelemen dalam form: 16 px.
- Lebar konten aplikasi: maksimal 1200 px. Form pengaturan: maksimal 640 px.
- Radius: 2 px untuk stempel dan tombol, 4 px untuk input dan label, 8 px untuk
  kartu dan panel. Tidak ada radius lain.
- Border: 1.5 px. Garis pemisah tabel: 1 px.
- Bayangan: hanya satu, untuk modal, `0 12px 32px rgba(31, 29, 26, 0.12)`.
  Tidak ada bayangan pada kartu, tombol, atau header.
- Tidak ada gradien, pola kisi, noise, atau tekstur gambar.

## 5. Ikon

Gunakan satu set SVG inline kecil dengan `stroke-width: 1.5`, ukuran 16, 20, atau
24 px, `currentColor`. Ikon yang diizinkan:

- `ic_clock`
- `ic_camera`
- `ic_calendar`
- `ic_users`
- `ic_map_pin`
- `ic_alert_triangle`
- `ic_check`
- `ic_x`
- `ic_arrow_right`
- `ic_menu`
- `ic_sun`
- `ic_moon`

Ikon hanya mendampingi label. Jangan memakai ikon tanpa teks untuk tindakan
penting. Jangan memakai pustaka ikon pihak ketiga.

## 6. Komponen

### 6.1 Tombol

Kelas dasar: `btn`. Varian: `btn-primary`, `btn-secondary`, `btn-ghost`,
`btn-danger`. Ukuran: `btn-sm` 32 px, `btn-md` 40 px, `btn-lg` 48 px.

- `btn-primary`: latar stempel, teks surface, radius 2 px, border 1.5 px
  transparan. Hover memakai stamp-deep. Fokus memakai cincin 2 px `--color-focus`
  dengan offset 2 px.
- `btn-secondary`: latar transparan, teks utama, border 1.5 px divider.
- `btn-ghost`: tanpa border, teks muted, hover latar tint.
- `btn-danger`: teks dan border absen, latar transparan. Konfirmasi wajib.
- Tombol nonaktif: opasitas 0.55, kursor `not-allowed`, tanpa perubahan warna.
- Saat memuat: teks diganti "Menyimpan..." dan tombol dinonaktifkan. Jangan
  memakai spinner berputar.

### 6.2 Form

- Label di atas input, 14 px, bobot 500, warna teks.
- Input: tinggi 40 px, latar surface, border 1.5 px divider, radius 4 px,
  padding 12 px. Fokus: border teks utama dan cincin 2 px `--color-focus`.
- Pesan bantuan 12 px muted, jarak 4 px dari input.
- Pesan galat 12 px warna absen, dengan ikon `ic_alert_triangle` 16 px.
- Input galat memakai border absen.
- Placeholder memakai muted dengan opasitas penuh. Jangan memakai placeholder
  sebagai pengganti label.
- Textarea minimal 3 baris. Select memakai chevron bawaan yang digayakan.
- Checkbox dan radio berukuran 16 px, label dapat diklik.
- Tidak ada form dua kolom di ponsel. Di desktop, form pengaturan boleh dua
  kolom dengan jarak 24 px.

### 6.3 Tabel buku besar

Kelas: `ledger`.

- Header tabel: latar tint, teks 12 px kapital, muted, bobot 600.
- Baris: border bawah 1 px divider, tinggi minimal 48 px.
- Angka rata kanan, teks rata kiri. Tanggal dan jam memakai tabular-nums.
- Hover baris: latar `color-mix(tint 60%, transparent)`.
- Baris terpilih: latar tint dan garis kiri 2 px stempel.
- Di ponsel, tabel menjadi daftar kartu. Setiap kartu menampilkan nama, status,
  waktu, dan tindakan. Jangan memaksa tabel melebar tanpa petunjuk.
- Untuk tabel lebar, bungkus dengan `overflow-x-auto` dan tampilkan petunjuk
  "Geser untuk melihat kolom lain." di atas tabel pada layar kecil.

### 6.4 Kartu statistik 2x2

Empat angka dalam satu panel, dipisahkan garis tipis, bukan empat kotak
terpisah. Susunan 2x2 di semua ukuran. Setiap sel berisi label 12 px muted,
angka 28 px tabular, dan keterangan 12 px. Garis pemisah memakai divider.
Jangan memakai ikon dekoratif di setiap sel.

### 6.5 Label status

Komponen `x-status` menampilkan titik 6 px dan teks 12 px. Warna mengikuti
status. Latar memakai campuran warna status 12 persen dengan surface. Radius
2 px. Tidak ada border.

Status dan label:

| Nilai | Label | Warna |
| --- | --- | --- |
| `hadir` | Hadir | present |
| `terlambat` | Terlambat | late |
| `izin` | Izin | leave |
| `sakit` | Sakit | sick |
| `cuti` | Cuti | leave |
| `libur` | Libur | muted |
| `absen` | Absen | absent |
| `belum_pulang` | Belum pulang | late |
| `belum_absen` | Belum absen | muted, tanpa latar, garis divider 1 px |
| `terjadwal` | Terjadwal | muted, tanpa latar, garis divider 1 px |

Label khusus:

- `tanpa_lokasi`: "Tanpa lokasi"
- `akurasi_rendah`: "Akurasi rendah"
- `di_luar_radius`: "Di luar radius"
- `diterima_manual`: "Diterima manual"
- `final_otomatis`: "Final otomatis"
- `perlu_ditinjau`: "Perlu ditinjau"
- `dibatalkan`: "Dibatalkan"

### 6.6 Stempel

Stempel adalah tanda visual persegi dengan border 2 px dan teks kapital 12 px,
sedikit miring 2 derajat. Dipakai hanya di halaman publik, halaman status, dan
kartu presensi. Jangan memakai stempel di setiap baris tabel.

### 6.7 Panel dan kartu

- Panel: latar surface, border 1.5 px divider, radius 8 px, padding 24 px.
- Kartu: latar card, border 1.5 px divider, radius 8 px, padding 16 px.
- Jangan menumpuk kartu di dalam kartu. Bila perlu pengelompokan, gunakan garis
  pemisah dan judul bagian.
- Header panel memakai garis bawah 1 px, bukan latar berbeda.

### 6.8 Modal

- Latar belakang `rgba(31, 29, 26, 0.45)`.
- Panel surface, radius 8 px, lebar maksimal 480 px, padding 24 px, satu bayangan.
- Judul 20 px, deskripsi 14 px muted.
- Tombol aksi di kanan bawah. Tindakan merusak memakai `btn-danger`.
- Fokus terperangkap di dalam modal. Escape menutup. Klik latar menutup hanya
  untuk modal informasi, bukan untuk form yang sudah diisi.

### 6.9 Toast

Toast muncul di kanan bawah, lebar maksimal 360 px, latar ink, teks paper,
radius 4 px, tanpa ikon dekoratif. Durasi 4 detik. Maksimal satu toast terlihat.
Untuk galat, teks tetap jelas dan tidak hanya mengandalkan warna.

### 6.10 Keadaan kosong, memuat, galat

- Kosong: judul 16 px, satu kalimat penjelasan, satu tindakan bila relevan.
  Tanpa ilustrasi. Contoh: "Belum ada anggota." / "Undang anggota pertama
  untuk mulai mencatat presensi." / tombol "Undang anggota".
- Memuat: teks "Memuat..." dengan opasitas 60 persen pada area yang dimuat.
  Tidak ada skeleton berkilau.
- Galat: panel dengan border absen, judul "Data gagal dimuat.", penjelasan
  singkat, tombol "Coba lagi".
- Kosong bukan galat. Jangan memakai warna absen untuk keadaan kosong.

## 7. Tata letak aplikasi

### 7.1 Shell aplikasi

- Sidebar kiri 240 px di desktop, dapat diciutkan menjadi 64 px.
- Top bar tinggi 56 px berisi judul halaman, pemilih tenant, dan menu pengguna.
- Konten maksimal 1200 px dengan padding 24 px.
- Di bawah 1024 px, sidebar menjadi drawer yang dibuka dari tombol menu.
- Di ponsel, top bar tetap terlihat dan judul halaman dipendekkan.
- Sidebar memakai latar paper, bukan surface. Item aktif memakai garis kiri
  2 px stempel dan teks utama. Item lain memakai muted.
- Jangan memakai sidebar gelap. Panel gelap hanya untuk halaman autentikasi dan
  bagian pemasaran tertentu.

Navigasi utama:

- Ringkasan
- Presensi
- Jadwal
- Izin dan cuti
- Tinjauan
- Laporan
- Anggota
- Lokasi
- Divisi
- Hari libur
- Pengaturan

Item yang tidak relevan dengan peran disembunyikan, bukan dinonaktifkan.

### 7.2 Halaman publik

- Lebar konten 1120 px, padding 24 px.
- Header: nama produk, tautan "Cara kerja", "Untuk siapa", "Pertanyaan",
  tombol "Masuk".
- Footer: nama produk, tautan "Masuk", "Daftar", "Privasi", "Ketentuan",
  dan satu kalimat "Dibuat untuk operasi harian di Indonesia."
- Tidak ada cookie banner karena tidak ada pelacakan pihak ketiga.
- Tidak ada angka pengguna, logo pelanggan, atau testimoni karangan.

### 7.3 Halaman autentikasi

- Dua kolom di desktop: panel kiri bergambar stempel besar dan satu kutipan
  pendek; panel kanan berisi form.
- Di ponsel, panel kiri menjadi header pendek dengan stempel.
- Panel kiri memakai latar ink dan teks paper. Panel kanan memakai paper.
- Form berada di panel surface dengan border, bukan kartu melayang.
- Judul form 28 px. Deskripsi 14 px muted. Tombol utama selebar form.
- Tautan bantuan di bawah tombol, 14 px.

## 8. Layar

Daftar layar minimum v1. Setiap layar memiliki satu tujuan utama.

### 8.1 Publik

1. **Beranda** (`/`)
   - Hero: "Absensi yang jelas, tanpa ribet." Subjudul satu kalimat.
   - Dua tombol: "Mulai dengan organisasi" dan "Masuk".
   - Pratinjau kartu presensi dengan label "Contoh tampilan".
   - Bagian masalah: tiga poin, tanpa kartu berikon.
   - Bagian cara kerja: empat langkah horizontal.
   - Bagian untuk siapa: tiga contoh nyata.
   - Bagian pertanyaan: empat pertanyaan.
   - Penutup dengan satu tindakan.
2. **Cara kerja** (`/cara-kerja`)
3. **Untuk siapa** (`/untuk-siapa`)
4. **Pertanyaan** (`/pertanyaan`)
5. **Masuk** (`/masuk`)
6. **Daftar** (`/daftar`)
7. **Lupa kata sandi** (`/lupa-kata-sandi`)
8. **Atur ulang kata sandi** (`/atur-ulang-kata-sandi/{token}`)
9. **Undangan** (`/undangan/{token}`)
10. **Status tenant** (`/status`)
11. **Privasi** (`/privasi`)
12. **Ketentuan** (`/ketentuan`)

### 8.2 Anggota

1. **Beranda anggota** (`/app`): kartu presensi hari ini, jadwal, dan status izin.
2. **Absen masuk** (`/app/presensi/masuk`): peta, jarak, tombol kamera.
3. **Absen pulang** (`/app/presensi/pulang`).
4. **Riwayat** (`/app/presensi`): daftar bulanan dengan filter.
5. **Detail hari** (`/app/presensi/{attendanceDay}`): waktu, lokasi, selfie, catatan.
6. **Jadwal** (`/app/jadwal`).
7. **Izin dan cuti** (`/app/izin`): daftar dan tombol "Ajukan izin".
8. **Form izin** (`/app/izin/baru`).
9. **Profil** (`/app/profil`).
10. **Pengaturan tampilan** (`/app/tampilan`): tema terang, gelap, atau sistem.
    Zona waktu organisasi ditampilkan sebagai keterangan, bukan pilihan.

### 8.3 Admin dan peninjau

1. **Ringkasan** (`/app/ringkasan`): statistik hari ini, perlu ditinjau, anggota
   belum absen.
2. **Presensi** (`/app/presensi`): tabel harian dengan filter lokasi, divisi,
   status.
3. **Detail presensi** (`/app/presensi/{attendanceDay}`).
4. **Tinjauan** (`/app/tinjauan`): antrean pengajuan.
5. **Jadwal** (`/app/jadwal`): daftar jadwal per anggota.
6. **Terapkan jadwal massal** (`/app/jadwal/terapkan`).
7. **Hari libur** (`/app/hari-libur`).
8. **Izin dan cuti** (`/app/izin`): semua pengajuan.
9. **Laporan** (`/app/laporan`): ringkasan bulanan dan ekspor CSV.
10. **Anggota** (`/app/anggota`): daftar, undang, ubah peran.
11. **Lokasi** (`/app/lokasi`): daftar dan form lokasi dengan peta.
12. **Divisi** (`/app/divisi`).
13. **Pengaturan** (`/app/pengaturan`): nama, zona waktu, jam kerja, geofence,
    selfie, retensi.
14. **Undangan** (`/app/anggota/undangan`): daftar undangan tertunda.

### 8.4 Platform admin

Platform admin masuk lewat halaman masuk yang sama di `/masuk` dan diarahkan ke
`/platform` setelah berhasil. Tidak ada halaman masuk terpisah. Bila pengguna
bukan platform admin membuka `/platform`, kembalikan 403.

1. **Daftar tenant** (`/platform/tenant`).
2. **Detail tenant** (`/platform/tenant/{tenant}`): status, paket, jumlah
   anggota. Tanpa data presensi atau selfie.
3. **Ubah status tenant**: aksi di halaman detail, bukan halaman terpisah.

## 9. Copy

Nada: tenang, langsung, tidak birokratis, tidak berlebihan. Sapa pengguna dengan
"Anda" di antarmuka. Gunakan "kami" hanya di halaman publik dan pesan sistem.

Aturan:

- Kalimat pendek. Satu gagasan per kalimat.
- Kata kerja di awal tombol: "Simpan", "Undang", "Ajukan", "Setujui", "Tolak".
- Jangan memakai "silakan" berulang. Cukup sekali bila perlu.
- Jangan memakai tanda seru.
- Jangan memakai emoji.
- Jangan memakai em dash. Gunakan titik, koma, atau titik dua.
- Angka memakai pemisah ribuan titik: 1.200. Waktu memakai format 24 jam: 08.15.
- Tanggal memakai format "30 Oktober 2026" di teks dan "30 Okt 2026" di tabel.
- Gunakan "organisasi" untuk tenant, "anggota" untuk membership, "peninjau"
  untuk reviewer, "pemilik" untuk owner, "admin" untuk admin.
- Gunakan "absen masuk" dan "absen pulang" untuk tindakan. Gunakan "presensi"
  untuk nama bagian. Gunakan "kehadiran" untuk konsep umum.

Contoh pesan:

- Berhasil: "Presensi tersimpan."
- Terlambat: "Anda tercatat masuk pukul 08.17."
- Di luar radius: "Anda berada 84 meter dari Kantor Pusat. Radius yang
  diizinkan 50 meter."
- GPS mati: "Lokasi tidak tersedia. Aktifkan izin lokasi lalu coba lagi."
- Izin disetujui: "Izin Anda untuk 30 Oktober 2026 disetujui."
- Kosong: "Belum ada pengajuan."
- Galat: "Data gagal dimuat. Coba lagi."

## 10. Gerak

- Durasi 120 sampai 200 ms. Easing `ease-out`.
- Hanya properti `opacity` dan `transform`.
- Stempel di hero boleh miring dari -2 ke 0 derajat saat hover, 200 ms.
- Tidak ada animasi masuk berulang, parallax, atau spinner berputar.
- Hormati `prefers-reduced-motion`: semua transisi menjadi 0 ms.

## 11. Aksesibilitas

- Semua tindakan dapat dijangkau dengan keyboard. Urutan fokus logis.
- Fokus terlihat dengan cincin 2 px dan offset 2 px. Jangan menghapus outline.
- Setiap input punya label yang terhubung. Pesan galat memakai `aria-describedby`.
- Tombol ikon punya `aria-label`.
- Tabel punya `caption` visual tersembunyi dan header `scope`.
- Status tidak hanya dibedakan warna. Selalu ada teks.
- Kontras teks minimal 4.5:1. Kontras komponen minimal 3:1.
- Target sentuh minimal 44x44 px di ponsel.
- Peta punya alternatif teks: alamat dan jarak.
- Bahasa halaman `lang="id"`.
- Jangan memakai `aria-live` untuk setiap perubahan kecil. Gunakan untuk toast
  dan hasil validasi form.

## 12. Peta

- Leaflet dengan tile OpenStreetMap.
- Tinggi peta 240 px di form lokasi, 320 px di layar absen.
- Peta tetap terang di mode gelap.
- Marker lokasi memakai pin stempel, bukan ikon pustaka.
- Lingkaran radius memakai garis stempel 1.5 px dengan isi 8 persen.
- Posisi pengguna memakai titik data dengan cincin surface.
- Selalu tampilkan teks jarak dan akurasi di bawah peta.
- Jangan memuat peta sebelum pengguna memberi izin lokasi. Tampilkan panel
  penjelasan dan tombol "Aktifkan lokasi".

## 13. Checklist sebelum menandai layar selesai

- [ ] Satu tindakan utama yang jelas.
- [ ] Tidak ada warna di luar token.
- [ ] Tidak ada radius, bayangan, atau gradien di luar aturan.
- [ ] Teks bahasa Indonesia, tanpa emoji dan em dash.
- [ ] Kontras teks dan komponen lulus.
- [ ] Dapat dipakai dengan keyboard.
- [ ] Keadaan kosong, memuat, dan galat ada.
- [ ] Tampil baik di 360 px, 768 px, 1024 px, dan 1440 px.
- [ ] Mode gelap diperiksa.
- [ ] Tidak ada klaim atau angka palsu.
