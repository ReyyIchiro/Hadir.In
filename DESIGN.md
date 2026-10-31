---
version: alpha
name: Hadir.In
description: Sistem presensi Indonesia yang terasa seperti buku absen resmi yang hidup. Editorial, tenang, presisi, dengan bukti waktu, tempat, dan wajah sebagai bahan utama desain.
colors:
  primary: "#B23A26"
  paper: "#F5F2EA"
  surface: "#FCFAF5"
  card: "#FFFFFF"
  ink: "#161A16"
  muted: "#666B61"
  secondary: "#4A5047"
  rule: "#D9D5C9"
  rule-strong: "#B9B4A6"
  seal: "#B23A26"
  seal-deep: "#8F2D1D"
  seal-tint: "#F3DED7"
  paper-deep: "#EDE9DE"
  tint: "#EFEBE1"
  present: "#2F6B4A"
  late: "#8A5A12"
  leave: "#2F5D78"
  sick: "#6A4A72"
  absent: "#8C332B"
  focus: "#161A16"
typography:
  display-xl:
    fontFamily: Newsreader Variable
    fontSize: 88px
    fontWeight: 400
    lineHeight: 0.94
    letterSpacing: -0.035em
  display-lg:
    fontFamily: Newsreader Variable
    fontSize: 64px
    fontWeight: 400
    lineHeight: 1
    letterSpacing: -0.03em
  display-md:
    fontFamily: Newsreader Variable
    fontSize: 40px
    fontWeight: 400
    lineHeight: 1.08
    letterSpacing: -0.025em
  title-lg:
    fontFamily: Newsreader Variable
    fontSize: 28px
    fontWeight: 500
    lineHeight: 1.15
    letterSpacing: -0.02em
  title-md:
    fontFamily: Newsreader Variable
    fontSize: 22px
    fontWeight: 500
    lineHeight: 1.25
    letterSpacing: -0.015em
  body-lg:
    fontFamily: Instrument Sans Variable
    fontSize: 17px
    fontWeight: 400
    lineHeight: 1.65
    letterSpacing: 0
  body-md:
    fontFamily: Instrument Sans Variable
    fontSize: 14px
    fontWeight: 400
    lineHeight: 1.55
    letterSpacing: 0
  body-sm:
    fontFamily: Instrument Sans Variable
    fontSize: 13px
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: 0
  label:
    fontFamily: Instrument Sans Variable
    fontSize: 12px
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: 0.08em
  data-lg:
    fontFamily: IBM Plex Mono
    fontSize: 28px
    fontWeight: 400
    lineHeight: 1.1
    letterSpacing: -0.02em
  data-md:
    fontFamily: IBM Plex Mono
    fontSize: 13px
    fontWeight: 400
    lineHeight: 1.4
    letterSpacing: 0
  data-sm:
    fontFamily: IBM Plex Mono
    fontSize: 11px
    fontWeight: 400
    lineHeight: 1.3
    letterSpacing: 0.02em
rounded:
  none: 0px
  seal: 2px
  input: 3px
  panel: 6px
  full: 9999px
spacing:
  "1": 4px
  "2": 8px
  "3": 12px
  "4": 16px
  "6": 24px
  "8": 32px
  "12": 48px
  "16": 64px
  "24": 96px
components:
  button-primary:
    backgroundColor: "{colors.seal}"
    textColor: "{colors.surface}"
    rounded: "{rounded.seal}"
    height: 40px
    padding: 16px
  button-primary-hover:
    backgroundColor: "{colors.seal-deep}"
    textColor: "{colors.surface}"
  theme-toggle:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.muted}"
    rounded: "{rounded.seal}"
    height: 32px
    width: 32px
  theme-toggle-hover:
    backgroundColor: "{colors.tint}"
    textColor: "{colors.ink}"
  button-secondary:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.ink}"
    rounded: "{rounded.seal}"
    height: 40px
    padding: 16px
  button-secondary-hover:
    backgroundColor: "{colors.tint}"
    textColor: "{colors.ink}"
  button-ghost:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.muted}"
    rounded: "{rounded.seal}"
    height: 40px
  button-ghost-hover:
    backgroundColor: "{colors.tint}"
    textColor: "{colors.ink}"
  button-danger:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.absent}"
    rounded: "{rounded.seal}"
    height: 40px
  field:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.input}"
    height: 40px
    padding: 12px
  panel:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.panel}"
    padding: 24px
  ledger-header:
    backgroundColor: "{colors.paper-deep}"
    textColor: "{colors.muted}"
    typography: "{typography.label}"
  nav-link:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.muted}"
    height: 35px
    padding: 12px
  nav-link-active:
    backgroundColor: "{colors.tint}"
    textColor: "{colors.ink}"
  status-present:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.present}"
  status-late:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.late}"
  status-leave:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.leave}"
  status-sick:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.sick}"
  status-absent:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.absent}"
  stamp:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.seal}"
    rounded: "{rounded.seal}"
    padding: 12px
  divider:
    backgroundColor: "{colors.rule}"
    height: 1px
  rule-strong:
    backgroundColor: "{colors.rule-strong}"
    height: 1px
  card:
    backgroundColor: "{colors.card}"
    textColor: "{colors.ink}"
    rounded: "{rounded.panel}"
  focus-ring:
    textColor: "{colors.focus}"
  ink-band:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.paper}"
---

# DESIGN.md

Spesifikasi visual kanonik Hadir.In versi 2. Dokumen ini mengikat semua layar,
komponen, dan copy antarmuka. Bila implementasi berbeda dari dokumen ini,
implementasi yang diperbaiki, kecuali dokumen ini diperbarui lebih dulu.

Dokumen lama disimpan di `docs/design/DESIGN-v1.md`. Dokumen ini tidak
memperpanjang kebiasaan lama. Dokumen ini menggantinya.

## Overview

Hadir.In adalah buku absen resmi yang hidup di layar. Bukan dashboard SaaS yang
kebetulan berisi data kehadiran, melainkan sebuah catatan yang dipercaya: ada
tanggal, ada nama, ada waktu, ada tempat, ada wajah, dan ada paraf.

Arah barunya bernama **Buku Besar Editorial**. Tiga kata yang harus terasa di
setiap layar:

1. **Tercatat.** Setiap elemen terlihat ditempatkan, bukan ditumpuk. Garis,
   kolom, nomor baris, dan tanggal membuat data terasa tersimpan.
2. **Diperiksa.** Waktu, lokasi, dan selfie adalah bukti, bukan hiasan. Desain
   menampilkan ketiganya sebagai satu kesatuan yang bisa dibaca cepat.
3. **Manusiawi.** Hangat, tetapi tidak lembut berlebihan. Kertas, tinta, dan
   cap memberi rasa dokumen; bahasa Indonesia yang tenang memberi rasa hormat.

Yang membuat desain ini khas Hadir.In, dan bukan template SaaS mana pun:

- **Serif editorial untuk pernyataan, sans untuk kerja, mono untuk bukti.**
  Judul memakai Newsreader, UI memakai Instrument Sans, angka dan metadata
  memakai IBM Plex Mono.
- **Garis vertikal yang terlihat.** Halaman publik memakai kolom bergaris
  seperti buku besar, bukan kartu mengambang.
- **Cap sebagai struktur, bukan stiker.** Cap tidak ditempel di mana-mana. Cap
  menandai satu peristiwa penting: status hari ini, satu tindakan, satu bukti.
- **Angka sebagai protagonis.** Jam masuk, jarak, dan akurasi ditampilkan besar,
  mono, dan rata kanan. Ini data yang orang datang untuk lihat.
- **Satu bidang gelap per halaman.** Bagian gelap dipakai untuk jeda dan
  penekanan, bukan untuk seluruh aplikasi.

Yang dihindari: gradien dekoratif, kaca buram, kartu bertumpuk dengan bayangan
besar, ilustrasi 3D, ikon garis seragam dari pustaka besar, ungu SaaS, dashboard
penuh kotak berwarna, sudut membulat besar, dan efek hover yang berlebihan.

Prinsip:

1. Kejelasan mengalahkan kepadatan. Satu layar punya satu pekerjaan utama.
2. Garis mengalahkan kotak. Pemisah tipis lebih sering dipakai daripada kartu.
3. Warna status hanya untuk status. Warna cap hanya untuk tindakan utama.
4. Angka rata kanan dan memakai angka tabular. Tanggal dan jam selalu jelas.
5. Tidak ada dekorasi yang tidak membawa informasi.
6. Setiap halaman punya satu momen yang layak diingat. Bila semuanya rata,
   tidak ada yang diingat.

## Dasar dan rujukan

Arah **Buku Besar Editorial** tidak lahir dari selera kosong. Bagian ini
mencatat sumber yang dipakai untuk menguji keputusan, pola yang sengaja
diambil, dan pola yang sengaja ditolak. Sumber dipakai untuk prinsip, bukan
untuk ditiru tampilannya.

### Kenapa banyak UI SaaS terasa generik

Penelusuran ke tulisan praktisi, diskusi desain, dan repositori publik
menemukan pola yang berulang. UI terasa seperti hasil generator bukan karena
kurang hiasan, tetapi karena keputusan kecil diserahkan ke default:

- **Palet default.** Gradien ungu atau indigo, glow di belakang hero, dan
  latar gelap kebiruan di seluruh produk.
- **Tipografi default.** Satu font sans untuk semua peran, biasanya Inter atau
  Geist, tanpa kontras antara pernyataan, kerja, dan data.
- **Struktur default.** Tiga kartu berikon, kartu di dalam kartu, dan bayangan
  lembut yang sama di setiap komponen.
- **Hero default.** Badge pil, judul gradien, dua tombol pil, dan mockup
  dashboard yang dimiringkan.
- **Isi default.** Angka pengguna, logo pelanggan, dan testimoni yang tidak
  bisa dibuktikan.

Istilah "AI slop" yang sering dipakai untuk pola ini merujuk pada hal yang
sama: permukaan terlihat rapi, tetapi tidak ada keputusan yang bisa
dipertanggungjawabkan di baliknya.

Sumber utama untuk diagnosis ini:

- [SaaSUI: Why SaaS UI looks AI generated](https://www.saasui.design/blog/saas-ui-looks-ai-generated)
- [Superdesign: Why AI design looks generic](https://superdesign.dev/blog/why-ai-design-looks-generic)
- [LogRocket: Linear design](https://blog.logrocket.com/ux-design/linear-design/)
- [r/UI_Design: AI fatigue from seeing same designs](https://www.reddit.com/r/UI_Design/comments/1tvb91q/ai_fatigue_from_seeing_same_designs/)
- [r/SaaS: How do you feel about AI generated user interfaces?](https://www.reddit.com/r/SaaS/comments/1tpes2s/how_do_you_feel_about_ai_generated_user_interfaces/)
- [r/reactjs: All the default shadcn/ui websites look so sloppy](https://www.reddit.com/r/reactjs/comments/1vv8060/all_the_default_shadcnui_websites_look_so_sloppy/)

Diskusi Reddit dipakai sebagai sinyal, bukan sebagai bukti. Yang diambil dari
diskusi adalah keluhan yang berulang, bukan selera satu kelompok.

### Yang diambil

| Prinsip | Sumber | Keputusan di Hadir.In |
| --- | --- | --- |
| Pisahkan peran tipografi | Refactoring UI, Practical Typography | Newsreader untuk pernyataan, Instrument Sans untuk kerja, IBM Plex Mono untuk bukti |
| Garis mengalahkan kartu | Diskusi shadcn/ui, SaaSUI | Pemisah 1 px, tabel buku besar, kolom 12, tanpa kartu bertumpuk |
| Satu momen gelap sebagai jeda | Analisis Linear, Stripe Press | Satu `ink-band` per halaman, bukan seluruh aplikasi gelap |
| Artefak nyata mengalahkan mockup | Diskusi r/SaaS, PostHog | Slip presensi, pita periode, dan tabel ledger, bukan dashboard miring |
| Warna aksen hanya untuk tindakan | Refactoring UI, GitHub Primer | Satu warna cap untuk tindakan utama dan penanda penting |
| Copy tenang dan spesifik | Diskusi r/SaaS, Practical Typography | Bahasa Indonesia sehari-hari, tanpa buzzword, tanpa angka palsu |
| Gerak kecil, bukan tontonan | Analisis Linear, panduan aksesibilitas | 120 sampai 200 ms, hanya opacity dan transform, hormati reduced motion |
| Dokumen sebagai sistem | GitHub Primer, spesifikasi DESIGN.md | Token dan aturan ditulis eksplisit, bukan diserahkan ke kebiasaan komponen |

### Yang ditolak

- **Linear dan Vercel sebagai template.** Bentuknya dipelajari, tetapi latar
  gelap, glow ungu, dan bento grid tidak dipakai. Produk ini dipakai pagi hari
  oleh admin dan di lapangan oleh anggota, bukan di editor kode.
- **Tiga kartu fitur.** Tiga bukti produk ditampilkan sebagai satu slip
  presensi dan satu pita periode, bukan tiga kartu berikon.
- **Mockup dashboard miring.** Halaman publik menampilkan artefak yang bisa
  dibaca, dengan label "Contoh tampilan, bukan data pelanggan".
- **Angka dan testimoni karangan.** Tidak ada logo pelanggan, jumlah pengguna,
  atau kutipan yang tidak bisa dibuktikan.
- **Efek dekoratif.** Tidak ada gradien, kaca buram, bayangan kartu, atau hover
  yang mengangkat elemen.
- **Ikon pihak ketiga yang seragam.** Ikon dipakai hanya bila membawa makna,
  dengan ukuran dan ketebalan yang konsisten.

### Rujukan lanjutan

- [GitHub Primer](https://primer.style/) untuk disiplin komponen, token, dan
  aksesibilitas.
- [Google Labs: DESIGN.md](https://github.com/google-labs-code/design.md/blob/main/docs/spec.md)
  untuk gagasan bahwa sistem desain bisa ditulis sebagai kontrak yang dibaca
  manusia dan mesin.
- [Refactoring UI](https://www.refactoringui.com/) untuk hierarki lewat ukuran,
  bobot, warna, dan spasi.
- [Butterick's Practical Typography](https://practicaltypography.com/) untuk
  panjang baris, skala, dan ritme teks.
- [Stripe Press](https://press.stripe.com/) dan [Notion](https://www.notion.com/)
  untuk rasa dokumen yang tenang di produk digital.
- [PostHog](https://posthog.com/) untuk contoh B2B yang berani tampil beda
  tanpa mengorbankan kepadatan data.
- [Mobbin](https://mobbin.com/) dan [Godly](https://godly.website/) untuk
  melihat alur produk nyata, bukan konsep Dribbble.

Tidak ada aset, ilustrasi, atau tata letak dari rujukan di atas yang disalin.
Yang diambil adalah prinsip, lalu diterjemahkan ke konteks presensi Indonesia.

## Colors

Palet dibangun dari kertas, tinta, dan satu warna cap. Hijau lumut menggantikan
krem generik sebagai dasar, sehingga kertas terasa segar dan tidak seperti
dokumen lama yang berdebu.

### Mode terang

| Token | Nilai | Pemakaian |
| --- | --- | --- |
| `--color-paper` | `#F5F2EA` | Latar halaman |
| `--color-surface` | `#FCFAF5` | Panel, tabel, form |
| `--color-card` | `#FFFFFF` | Kartu ringan di atas surface |
| `--color-ink` | `#161A16` | Teks utama, bidang gelap |
| `--color-muted` | `#666B61` | Teks sekunder, label, keterangan |
| `--color-secondary` | `#4A5047` | Teks pendukung yang perlu lebih tegas |
| `--color-rule` | `#D9D5C9` | Garis pemisah dan border |
| `--color-rule-strong` | `#B9B4A6` | Garis struktural, border input |
| `--color-seal` | `#B23A26` | Tindakan utama, tanda penting |
| `--color-seal-deep` | `#8F2D1D` | Hover dan pressed |
| `--color-seal-tint` | `#F3DED7` | Latar lembut untuk cap |
| `--color-paper-deep` | `#EDE9DE` | Pita bagian, latar header tabel |
| `--color-tint` | `#EFEBE1` | Baris terpilih, hover |
| `--color-present` | `#2F6B4A` | Status hadir |
| `--color-late` | `#8A5A12` | Status terlambat |
| `--color-leave` | `#2F5D78` | Status izin dan cuti |
| `--color-sick` | `#6A4A72` | Status sakit |
| `--color-absent` | `#8C332B` | Status absen |
| `--color-focus` | `#161A16` | Cincin fokus |

Kontras terukur di mode terang:

| Pasangan | Rasio | Minimum |
| --- | --- | --- |
| teks pada paper | 15.73:1 | 4.5:1 |
| teks pada surface | 16.87:1 | 4.5:1 |
| muted pada paper | 4.89:1 | 4.5:1 |
| muted pada surface | 5.24:1 | 4.5:1 |
| cap pada paper | 5.32:1 | 4.5:1 |
| surface pada cap | 5.71:1 | 4.5:1 |
| hadir pada surface | 6.06:1 | 4.5:1 |
| terlambat pada surface | 5.67:1 | 4.5:1 |
| izin pada surface | 6.81:1 | 4.5:1 |
| sakit pada surface | 7.10:1 | 4.5:1 |
| absen pada surface | 7.67:1 | 4.5:1 |

### Mode gelap

| Token | Nilai |
| --- | --- |
| `--color-paper` | `#111411` |
| `--color-surface` | `#171B17` |
| `--color-card` | `#1B201B` |
| `--color-ink` | `#0C0F0C` |
| `--color-muted` | `#A3A89C` |
| `--color-secondary` | `#C4C8BC` |
| `--color-rule` | `#2C332C` |
| `--color-rule-strong` | `#3E463E` |
| `--color-seal` | `#E07054` |
| `--color-seal-deep` | `#F08A70` |
| `--color-seal-tint` | `#3A211B` |
| `--color-paper-deep` | `#0C0F0C` |
| `--color-tint` | `#1F251F` |
| `--color-present` | `#7FC49A` |
| `--color-late` | `#D9B36A` |
| `--color-leave` | `#8FB3C9` |
| `--color-sick` | `#C0A0C8` |
| `--color-absent` | `#E09A94` |
| `--color-focus` | `#EDE8DC` |

Kontras mode gelap: teks 15.17:1 pada paper, muted 7.63:1, cap 5.84:1, hadir
8.51:1, terlambat 8.80:1, izin 7.85:1, sakit 7.55:1, absen 7.65:1.

### Aturan pemakaian warna

- Cap hanya untuk satu tindakan utama per layar dan untuk penanda "Perlu
  ditinjau". Jangan memakai cap untuk tautan biasa.
- Warna status dipakai sebagai teks atau titik kecil, bukan latar kartu penuh.
  Latar status memakai `color-mix` 10 persen dengan surface.
- Jangan memakai lebih dari satu warna aksen di satu komponen.
- Hijau lumut dipakai untuk kedalaman dan ketenangan, bukan untuk status hadir.
  Status hadir tetap memakai `--color-present`.
- Peta tetap terang di kedua mode. Jangan menerapkan filter gelap ke tile peta.
- Bidang gelap memakai `--color-ink` dengan teks `--color-paper`. Jangan memakai
  hitam murni.

## Typography

Tiga peran huruf, satu alasan untuk masing-masing:

- **Newsreader Variable** untuk pernyataan, judul halaman, dan angka besar di
  halaman publik. Serif ini punya ketegangan editorial dan tetap terbaca di
  ukuran kecil. Bobot 400 sampai 600. Dimuat dari server sendiri.
- **Instrument Sans Variable** untuk teks isi, tabel, form, tombol, dan seluruh
  antarmuka aplikasi. Bobot 400, 500, 600. Dimuat dari server sendiri.
- **IBM Plex Mono** untuk angka, tanggal, koordinat, kode, dan metadata. Bobot
  400 dan 500. Dimuat dari server sendiri.

Fallback: `system-ui, -apple-system, "Segoe UI", sans-serif` untuk sans,
`Georgia, "Times New Roman", serif` untuk serif, dan
`ui-monospace, "SFMono-Regular", monospace` untuk mono.

Skala:

| Nama | Ukuran | Tinggi baris | Pemakaian |
| --- | --- | --- | --- |
| `display-xl` | 88 px | 0.94 | Hero publik di desktop |
| `display-lg` | 64 px | 1.0 | Hero publik di tablet |
| `display-md` | 40 px | 1.08 | Judul bagian publik |
| `title-lg` | 28 px | 1.15 | Judul halaman aplikasi |
| `title-md` | 22 px | 1.25 | Judul panel |
| `body-lg` | 17 px | 1.65 | Paragraf pengantar |
| `body-md` | 14 px | 1.55 | Teks tabel, form, tombol |
| `body-sm` | 13 px | 1.5 | Keterangan, bantuan |
| `label` | 12 px | 1.2 | Eyebrow, header tabel |
| `data-lg` | 28 px | 1.1 | Jam, angka utama |
| `data-md` | 13 px | 1.4 | Angka tabel, koordinat |
| `data-sm` | 11 px | 1.3 | Metadata kecil |

Aturan:

- Judul serif memakai `letter-spacing: -0.025em` sampai `-0.035em`. Jangan
  memakai bobot 700 pada serif; bobot 400 sampai 600 sudah cukup.
- Teks isi maksimal 68 karakter per baris.
- Label semua kapital hanya untuk eyebrow dan header tabel. Ukuran 12 px,
  `letter-spacing: 0.08em`, bobot 600.
- Angka selalu `font-variant-numeric: tabular-nums`. Jam memakai titik: 08.15.
- Jangan memakai lebih dari tiga bobot dalam satu layar.
- Jangan memakai huruf miring untuk penekanan. Gunakan bobot atau warna.
- Jangan memakai `text-transform: uppercase` pada kalimat panjang.

## Layout

Tata letak memakai kolom yang terlihat. Halaman publik terasa seperti halaman
buku besar: ada margin, ada kolom, ada nomor bagian. Aplikasi terasa seperti
lembar kerja yang sama, tetapi lebih rapat.

- Lebar konten publik: maksimal 1200 px, padding 24 px di ponsel dan 48 px di
  desktop.
- Lebar konten aplikasi: maksimal 1280 px, padding 24 px.
- Grid publik: 12 kolom, jarak 24 px. Garis kolom hanya muncul di bagian yang
  memang perlu dibaca sebagai tabel, bukan di seluruh halaman.
- Grid aplikasi: 12 kolom, jarak 24 px. Panel boleh membentang 4, 6, 8, atau
  12 kolom.
- Spasi mengikuti kelipatan 4: 4, 8, 12, 16, 24, 32, 48, 64, 96.
- Jarak antarbagian publik: 96 px di desktop, 64 px di ponsel.
- Jarak antarelemen form: 16 px. Jarak antarkelompok form: 32 px.
- Form pengaturan: maksimal 640 px.
- Tidak ada konten yang melebar penuh tanpa alasan. Setiap bagian punya margin
  yang jelas.

### Ritme halaman publik

Setiap halaman publik memakai urutan ritme ini:

1. **Pembuka terang.** Judul serif besar, satu paragraf, satu tindakan.
2. **Bukti.** Satu objek visual yang menunjukkan cara kerja, bukan ilustrasi.
3. **Bidang gelap.** Satu bagian dengan latar ink untuk jeda dan penekanan.
4. **Rincian terang.** Grid bergaris, daftar, atau tabel.
5. **Penutup.** Satu kalimat dan satu tindakan.

Jangan memakai dua bidang gelap berturut-turut. Jangan memakai bidang gelap
hanya untuk footer.

### Margin dan folio

- Halaman publik memakai nomor bagian kecil di margin kiri, format `01`, `02`,
  dan seterusnya, memakai mono 11 px.
- Nomor bagian bukan navigasi. Jangan membuatnya terlihat seperti tautan.
- Di ponsel, nomor bagian tetap ada tetapi berada di atas judul, bukan di margin.

## Elevation & Depth

Kedalaman dibuat dengan lapisan warna dan garis, bukan bayangan.

- Latar paper, surface, dan paper-deep sudah cukup untuk memisahkan lapisan.
- Bayangan hanya untuk modal: `0 16px 48px rgb(12 15 12 / 0.18)`.
- Tidak ada bayangan pada kartu, tombol, header, atau sidebar.
- Tidak ada gradien. Pengecualian: tidak ada.
- Tidak ada kaca buram, blur, atau transparansi dekoratif.
- Efek hover tidak boleh mengangkat elemen. Perubahan hanya warna, garis, atau
  posisi 1 px.

## Shapes

Bentuk mengikuti alat tulis dan dokumen, bukan aplikasi mobile generik.

- Radius tombol dan cap: 2 px.
- Radius input dan label: 3 px.
- Radius panel dan kartu: 6 px.
- Tidak ada radius 12 px atau lebih, kecuali elemen yang memang bundar: avatar,
  titik status, dan tombol bantuan mengambang.
- Border: 1 px untuk garis struktural, 1.5 px untuk input dan tombol.
- Sudut tajam adalah bagian dari karakter. Jangan membulatkan semua hal.
- Garis pemisah horizontal lebih tipis dari garis struktural vertikal.

## Merek

Mark Hadir.In adalah huruf H yang dibentuk dua bilah arang dan satu figur
bercap, dengan centang cap sebagai satu-satunya warna. Mark menyatakan dua hal
sekaligus: kehadiran seseorang dan tanda tangan yang mengesahkannya.

- **Komposisi.** Dua bilah vertikal arang, satu titik kepala, satu centang cap
  yang menembus bidang. Jangan memisahkan, memutar, atau menumpuk unsur ini.
- **Warna.** Arang `#2C2B34` dan cap `#B54B2C`. Di permukaan gelap, pakai
  varian `hadir-in-mark-light.png` yang mengganti bilah arang menjadi paper;
  latarnya tetap transparan. Komponen `<x-brand-mark>` merender kedua varian
  dan CSS menukarnya lewat `html[data-theme='dark']`, jadi jangan memilih
  varian secara manual di view. Prop `light` hanya untuk panel yang selalu
  gelap, seperti panel masuk. Jangan mewarnai ulang mark dengan warna status
  atau warna tema.
- **Ruang kosong.** Sisakan minimal setengah tinggi mark di semua sisi. Jangan
  menempelkan mark ke tepi panel, garis, atau teks lain tanpa jarak.
- **Ukuran.** Di wordmark, tinggi mark mengikuti `1.15em` dari font-size induk
  lewat kelas `brand-mark`. Jangan memberi ukuran piksel tetap yang memutus
  kesejajaran dengan teks.
- **Wordmark.** Selalu "Hadir.In" dengan titik, bukan "HadirIn" atau
  "Hadir.in". Pakai komponen `<x-wordmark>`: mark di kiri, teks 17 px display
  semibold, jarak 8 px. Lockup diratakan pada garis dasar, bukan tengah kotak
  baris, supaya kaki mark dan kaki huruf duduk di garis yang sama; jangan
  menyusun ulang mark dan teks secara manual di view mana pun. Label sekunder
  seperti "Platform" memakai prop `label`, tampil sans 12 px muted dan ikut
  garis dasar. Jangan menambahkan tagline di dalam lockup.
- **Latar.** Mark berwarna, jadi jangan menaruhnya di atas foto, gradien, atau
  warna status. Latar yang sah hanya paper, surface, dan bidang ink.
- **Favicon dan ikon aplikasi.** `favicon.ico` untuk peramban, `icon-192` dan
  `icon-512` untuk manifest, `icon-maskable-512` untuk Android, dan
  `apple-touch-icon` 180 px untuk iOS. Semua berasal dari mark yang sama.
- **Kartu pratinjau.** `og-default.png` 1200x630 memuat mark dan wordmark di
  atas paper. Jangan membuat kartu pratinjau per halaman.

Berkas kanonik ada di `public/brand/`. Sumber kuadrat ada di
`Hadir.In Logo.png` di akar repo. Bila mark berubah, semua turunan di
`public/brand/` harus dibuat ulang dari sumber itu, bukan dari tangkapan layar.

## Components

### Tombol

Kelas dasar: `btn`. Varian: `btn-primary`, `btn-secondary`, `btn-ghost`,
`btn-danger`. Ukuran: `btn-sm` 32 px, `btn-md` 40 px, `btn-lg` 48 px. Di layar
di bawah 640 px, `btn-sm` naik menjadi minimal 44 px agar target sentuh tetap
memenuhi ambang aksesibilitas.

- `btn-primary`: latar cap, teks surface, radius 2 px, border 1.5 px
  transparan. Hover memakai seal-deep. Fokus memakai cincin 2 px
  `--color-focus` dengan offset 2 px.
- `btn-secondary`: latar transparan, teks utama, border 1.5 px rule-strong.
- `btn-ghost`: tanpa border, teks muted, hover latar tint.
- `btn-danger`: teks dan border absen, latar transparan. Konfirmasi wajib.
- Tombol nonaktif: opasitas 0.55, kursor `not-allowed`, tanpa perubahan warna.
- Saat memuat: teks diganti "Menyimpan..." dan tombol dinonaktifkan. Jangan
  memakai spinner berputar.
- Tombol utama di halaman publik boleh memakai panah kecil di kanan. Jangan
  memakai ikon di dalam tombol aplikasi kecuali ikon tersebut sudah ada di
  set ikon resmi.

### Form

- Label di atas input, 14 px, bobot 500, warna teks.
- Input: tinggi 40 px, latar surface, border 1.5 px rule-strong, radius 3 px,
  padding 12 px. Fokus: border teks utama dan cincin 2 px `--color-focus`.
- Pesan bantuan 12 px muted, jarak 4 px dari input.
- Input tanggal native mengikuti locale browser. Selalu tampilkan tanggal
  terpilih dalam format panjang Indonesia sebagai pesan bantuan di bawah
  input, misalnya "30 Oktober 2026".
- Pesan galat 12 px warna absen, dengan ikon `alert-triangle` 16 px.
- Input galat memakai border absen.
- Placeholder memakai muted dengan opasitas penuh. Jangan memakai placeholder
  sebagai pengganti label.
- Textarea minimal 3 baris. Select memakai chevron bawaan yang digayakan.
- Checkbox dan radio berukuran 16 px, label dapat diklik.
- Tidak ada form dua kolom di ponsel. Di desktop, form pengaturan boleh dua
  kolom dengan jarak 24 px.
- Grup form yang panjang dipisah dengan garis 1 px dan judul bagian 12 px
  kapital, bukan kartu.

### Tabel buku besar

Kelas: `ledger`.

Tabel memakai tata bahasa kolom, bukan perataan ad hoc per sel. Setiap tabel
punya satu kolom utama yang mengambil 30% lebar, kolom teks sekunder yang
berbagi sisa ruang, kolom pendek yang tidak membungkus, dan perataan yang
ditentukan oleh jenis datanya.

| Peran | Kelas | Perilaku |
| --- | --- | --- |
| Kolom utama | `cell-grow` | Satu per tabel. Lebar 30%; sisa lebar dibagi ke kolom teks sekunder. |
| Kolom pendek | `cell-fit` | Lebar 1%, tidak membungkus. |
| Tanggal | `cell-date` | Rata kiri, mono, tabular. |
| Jam | `cell-time` | Rata kanan, mono, tabular. |
| Angka | `cell-num` | Rata kanan, mono, tabular. |
| Status | `cell-status` | Lebar 1%. Pil status membawa `nowrap` sendiri. |
| Tindakan | `cell-action` | Lebar 1%, rata kanan, tidak membungkus. |
| Metadata | `cell-meta` | 12 px, muted. Boleh digabung dengan `cell-date`. |

- Header tabel: latar paper-deep, teks 11 px kapital, muted, bobot 600,
  tracking 0.08em, padding 10 px 16 px.
- Baris: border bawah 1 px rule, tinggi 48 px, padding sel 12 px 16 px.
  Padding kiri kolom pertama dan padding kanan kolom terakhir 16 px di ponsel,
  24 px di desktop.
- Tanggal rata kiri karena dibaca sebagai identitas baris. Jam dan angka rata
  kanan karena dibandingkan sebagai besaran. Jangan membalik ini.
- Header kolom selalu sejajar dengan datanya. Header jam dan angka memakai
  `num-head`. Header tanggal tidak memakai `num-head`.
- `tabular-nums` hanya fitur angka, bukan perataan. Jangan mengandalkannya
  untuk membuat kolom rapi; pakai kelas peran di atas.
- Mono dan ukuran 13 px hanya untuk isi sel data. Header tetap memakai aturan
  header tabel.
- Jangan memakai `text-right` lepas pada sel tabel; gunakan `cell-action` atau
  `cell-num` supaya lebar kolom ikut benar.
- Kolom pertama boleh memakai nomor baris mono 11 px muted. Ini memperkuat rasa
  buku besar, bukan dekorasi.
- Hover baris: latar `color-mix(tint 60%, transparent)`.
- Baris terpilih: latar tint dan garis kiri 2 px cap.
- Di ponsel, tabel data utama yang sempit (riwayat pribadi, antrean tinjauan)
menjadi daftar kartu. Tabel lebar dengan banyak kolom (presensi tim, laporan)
boleh tetap berupa tabel dengan `overflow-x-auto` dan petunjuk geser. Jangan
memaksa tabel melebar tanpa petunjuk.
- Untuk tabel lebar, bungkus dengan `overflow-x-auto` dan tampilkan petunjuk
  "Geser untuk melihat kolom lain." di atas tabel pada layar kecil.
- Jangan memakai zebra stripe. Garis sudah cukup.

### Kartu statistik 2x2

Empat angka dalam satu panel, dipisahkan garis tipis, bukan empat kotak
terpisah. Susunan 2x2 di semua ukuran. Setiap sel berisi label 12 px muted,
angka 28 px mono tabular, dan keterangan 12 px. Garis pemisah memakai rule.
Jangan memakai ikon dekoratif di setiap sel.

Untuk ringkasan admin, angka besar boleh memakai serif 40 px bila angka itu
adalah satu-satunya fokus bagian. Jangan mencampur serif dan mono dalam satu
kelompok angka.

### Label status

Komponen `x-status` menampilkan titik 6 px dan teks 12 px. Warna mengikuti
status. Latar memakai campuran warna status 10 persen dengan surface. Radius
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
| `belum_absen` | Belum absen | muted, tanpa latar, garis rule 1 px |
| `terjadwal` | Terjadwal | muted, tanpa latar, garis rule 1 px |

Label khusus:

- `tanpa_lokasi`: "Tanpa lokasi"
- `akurasi_rendah`: "Akurasi rendah"
- `di_luar_radius`: "Di luar radius"
- `diterima_manual`: "Diterima manual"
- `final_otomatis`: "Final otomatis"
- `perlu_ditinjau`: "Perlu ditinjau"
- `dibatalkan`: "Dibatalkan"

### Cap

Cap adalah tanda visual persegi dengan border 2 px dan teks kapital 12 px,
sedikit miring 2 derajat. Cap dipakai hanya untuk:

- status hari ini di kartu presensi,
- satu tindakan penting di halaman publik,
- penanda "Perlu ditinjau" di antrean.

Jangan memakai cap di setiap baris tabel. Jangan memakai cap sebagai hiasan
judul. Jangan memakai lebih dari satu cap dalam satu layar, kecuali halaman
detail presensi yang memang menampilkan status dan bukti.

### Panel dan kartu

- Panel: latar surface, border 1 px rule, radius 6 px, padding 24 px.
- Kartu: latar card, border 1 px rule, radius 6 px, padding 16 px.
- Jangan menumpuk kartu di dalam kartu. Bila perlu pengelompokan, gunakan garis
  pemisah dan judul bagian.
- Header panel memakai garis bawah 1 px, bukan latar berbeda.
- Panel yang memuat tabel tidak memakai padding di sisi tabel. Header dan isi
  tabel menyentuh tepi panel.

### Modal

- Latar belakang `rgb(12 15 12 / 0.55)`.
- Panel surface, radius 6 px, lebar maksimal 480 px, padding 24 px, satu bayangan.
- Judul 22 px serif, deskripsi 14 px muted.
- Tombol aksi di kanan bawah. Tindakan merusak memakai `btn-danger`.
- Fokus terperangkap di dalam modal. Escape menutup. Klik latar menutup hanya
  untuk modal informasi, bukan untuk form yang sudah diisi.

### Toast

Toast muncul di kanan bawah, lebar maksimal 360 px, latar ink, teks paper,
radius 2 px, tanpa ikon dekoratif. Durasi 4 detik. Maksimal satu toast terlihat.
Untuk galat, teks tetap jelas dan tidak hanya mengandalkan warna.

### Keadaan kosong, memuat, galat

- Kosong: judul 16 px, satu kalimat penjelasan, satu tindakan bila relevan.
  Tanpa ilustrasi. Contoh: "Belum ada anggota." / "Undang anggota pertama
  untuk mulai mencatat presensi." / tombol "Undang anggota".
- Memuat: teks "Memuat..." dengan opasitas 60 persen pada area yang dimuat.
  Tidak ada skeleton berkilau.
- Galat: panel dengan border absen, judul "Data gagal dimuat.", penjelasan
  singkat, tombol "Coba lagi".
- Kosong bukan galat. Jangan memakai warna absen untuk keadaan kosong.

### Komponen khas: Tiga Bukti

Komponen ini adalah wajah visual Hadir.In. Isinya tiga bukti yang selalu
muncul bersamaan saat orang absen:

1. **Waktu.** Jam besar mono, misalnya `07.56`, dengan label "Masuk".
2. **Tempat.** Jarak dan nama lokasi, misalnya `12 m dari Kantor Pusat`.
3. **Wajah.** Kotak selfie kecil dengan label "Selfie tersimpan privat".

Aturan:

- Ketiganya tampil dalam satu baris di desktop, bertumpuk di ponsel.
- Masing-masing dipisahkan garis vertikal 1 px, bukan kartu.
- Waktu adalah elemen terbesar. Tempat dan wajah lebih kecil.
- Jangan memakai placeholder wajah generik. Bila tidak ada selfie, tampilkan
  kotak kosong dengan label "Selfie tidak tersedia".
- Komponen ini dipakai di beranda publik, halaman detail presensi, dan kartu
  presensi hari ini. Jangan memakainya di tabel.

### Komponen khas: Tombol Tema

Tombol 32 px persegi, radius 2 px, tanpa bayangan dan tanpa border terlihat.
Isinya satu ikon 18 px: bulan pada mode terang, matahari pada mode gelap. Ikon
menyatakan tema tujuan, bukan tema sekarang.

Aturan:

- Tombol muncul di top bar aplikasi, header publik, header platform, dan sudut
  kanan atas halaman autentikasi. Satu tombol per halaman.
- Perpindahan tema terjadi seketika, tanpa transisi halaman. Tidak ada menu
  dropdown dan tidak ada pilihan ketiga di tombol; pilihan "Ikuti sistem"
  hanya ada di halaman Tampilan.
- Tombol memakai `aria-pressed` yang mencerminkan mode gelap aktif dan
  `aria-label` "Ganti tema terang atau gelap". Ikon selalu `aria-hidden`.
- Fokus keyboard memakai outline 2 px cap dengan offset 2 px.
- Tema ditentukan sebelum CSS dimuat. Jangan memindahkan bootstrap tema ke
  dalam bundel JavaScript, karena itu menimbulkan kedipan tema terang.

### Komponen khas: Pita Periode

Pita tipis di atas tabel atau laporan yang menyatakan periode yang sedang
dibaca, misalnya `OKTOBER 2026` di kiri dan `31 hari kerja` di kanan. Latar
paper-deep, teks mono 11 px, border atas dan bawah 1 px. Pita ini menggantikan
judul bagian yang bertele-tele.

### Komponen khas: Tombol Bantuan

Satu tombol bundar 48 px di kanan bawah halaman publik dan autentikasi. Saat
disentuh atau difokus, tombol melebar menjadi pil dan memunculkan label "Butuh
bantuan?" tanpa memindahkan posisi ikon. Bentuk bundar adalah pengecualian yang
disengaja: ia meniru stempel bulat yang ditempel di sudut kertas, bukan kartu.

Aturan:

- Hanya di layout publik dan autentikasi. Di dalam aplikasi, jalur bantuan
  pertama adalah admin organisasi; tombol ini tidak dipasang di sana.
- Isinya ikon WhatsApp 20 px dan label 13 px. Warna memakai teks dan surface,
  bukan warna cap, supaya tidak bersaing dengan satu tindakan utama halaman.
- Membuka `wa.me` di tab baru dengan pesan yang sudah berisi nama, organisasi,
  dan kendala. Jangan membuka dialog chat di dalam aplikasi.
- Nomor diambil dari `SUPPORT_WHATSAPP`. Bila kosong, komponen tidak merender
  apa pun. Jangan menulis nomor langsung di view.
- Posisi `1rem` dari kanan dan bawah, ditambah `env(safe-area-inset-bottom)`.
  Z-index di bawah modal dan dialog konfirmasi.
- Di layar sentuh tombol tetap bundar dan hanya ikon yang tampil; label muncul
  saat hover atau fokus keyboard saja. Jangan memaksa label terbuka di ponsel,
  karena ia menutupi isi halaman. Halaman yang memakai tombol ini menyisakan
  ruang bawah setidaknya 6rem agar tombol tidak menimpa teks terakhir.
- Gerak memakai transisi `max-width` 180 ms dan dimatikan saat pengguna meminta
  gerak dikurangi.

## Do's and Don'ts

- Do pakai serif hanya untuk pernyataan dan angka fokus. Jangan memakai serif
  untuk teks tabel atau tombol.
- Do pakai mono untuk semua angka yang dibandingkan. Jangan memakai mono untuk
  paragraf.
- Do biarkan garis dan ruang kosong bekerja. Jangan menambah kartu ketika ragu.
- Do tampilkan satu bukti visual per bagian. Jangan mengisi setiap bagian
  dengan ilustrasi.
- Do pakai bidang gelap untuk satu bagian per halaman. Jangan memakainya untuk
  seluruh aplikasi.
- Do pertahankan radius kecil. Jangan membulatkan panel hingga terasa seperti
  aplikasi konsumen.
- Do pakai warna status hanya pada data status. Jangan mewarnai seluruh baris
  tabel karena statusnya.
- Don't pakai gradien, blur, noise, atau tekstur gambar.
- Don't pakai ikon dari pustaka pihak ketiga. Gunakan set ikon resmi.
- Don't pakai emoji, em dash, atau tanda seru di antarmuka.
- Don't pakai angka atau testimoni karangan di halaman publik.
- Don't pakai animasi masuk berulang, parallax, atau spinner berputar.

## Tata letak aplikasi

### Shell aplikasi

- Sidebar kiri 240 px di desktop dan tetap terlihat di atas 1024 px. Tidak ada mode ciut; di bawah 1024 px sidebar menjadi drawer.
- Top bar tinggi 56 px berisi running head, zona waktu, dan avatar. Running
  head adalah nama halaman dalam mono 11 px kapital muted, bukan judul kedua.
  Judul halaman yang sebenarnya hanya ada satu, yaitu `h1` di dalam konten.
- Konten maksimal 1280 px dengan padding 24 px.
- Di bawah 1024 px, sidebar menjadi drawer yang dibuka dari tombol menu.
- Di ponsel, top bar tetap terlihat dan judul halaman dipendekkan.
- Sidebar memakai latar paper, bukan surface. Item aktif memakai garis kiri
  2 px cap dan teks utama. Item lain memakai muted.
- Jangan memakai sidebar gelap. Panel gelap hanya untuk halaman autentikasi dan
  bagian pemasaran tertentu.
- Di atas daftar navigasi, tampilkan nama organisasi dan peran dalam blok
  bergaris, bukan kartu.

Navigasi utama (tanpa label grup):

- Ringkasan
- Presensi
- Jadwal
- Izin dan cuti
- Tinjauan
- Laporan

Grup kedua berlabel "Kelola":

- Anggota
- Lokasi
- Divisi
- Hari libur
- Pengaturan

Item yang tidak relevan dengan peran disembunyikan, bukan dinonaktifkan.

### Halaman publik

- Lebar konten 1200 px, padding 24 px di ponsel dan 48 px di desktop.
- Header: nama produk, tautan "Cara kerja", "Untuk siapa", "Pertanyaan",
  tombol "Masuk" dan "Daftar".
- Header memakai garis bawah 1 px. Jangan memakai latar transparan atau sticky
  blur.
- Footer: nama produk, tautan "Masuk", "Daftar", "Privasi", "Ketentuan",
  dan satu kalimat "Dibuat untuk operasi harian di Indonesia."
- Tidak ada cookie banner karena tidak ada pelacakan pihak ketiga.
- Tidak ada angka pengguna, logo pelanggan, atau testimoni karangan.

### Halaman autentikasi

- Dua kolom di desktop: panel kiri gelap dengan satu pernyataan serif dan satu
  cap; panel kanan berisi form.
- Di ponsel, panel kiri menjadi header pendek dengan cap.
- Panel kiri memakai latar ink dan teks paper. Panel kanan memakai paper.
- Form berada di panel surface dengan border, bukan kartu melayang.
- Judul form 28 px serif. Deskripsi 14 px muted. Tombol utama selebar form.
- Tautan bantuan di bawah tombol, 14 px.

## Layar

Daftar layar minimum v1. Setiap layar memiliki satu tujuan utama.

### Publik

1. **Beranda** (`/`)
   - Hero: "Absensi yang jelas, tanpa ribet." Subjudul satu kalimat.
   - Dua tombol: "Mulai dengan organisasi" dan "Masuk".
   - Komponen Tiga Bukti sebagai pratinjau, dengan label "Contoh tampilan".
   - Bagian masalah: tiga poin bernomor di margin, tanpa kartu berikon.
   - Bagian cara kerja: empat langkah horizontal dengan garis vertikal.
   - Bagian gelap: satu pernyataan tentang bukti dan kepercayaan.
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

### Anggota

1. **Beranda anggota** (`/app`): Tiga Bukti hari ini, jadwal, dan status izin.
2. **Absen masuk** (`/app/presensi/masuk`): peta, jarak, tombol kamera.
3. **Absen pulang** (`/app/presensi/pulang`).
4. **Riwayat** (`/app/presensi`): daftar bulanan dengan filter dan Pita Periode.
5. **Detail hari** (`/app/presensi/{attendanceDay}`): waktu, lokasi, selfie,
   catatan, dan jejak audit.
6. **Jadwal** (`/app/jadwal`).
7. **Izin dan cuti** (`/app/izin`): daftar dan tombol "Ajukan izin".
8. **Form izin** (`/app/izin/baru`).
9. **Profil** (`/app/profil`).
10. **Pengaturan tampilan** (`/app/tampilan`): tema terang, gelap, atau sistem.
    Zona waktu organisasi ditampilkan sebagai keterangan, bukan pilihan.

### Admin dan peninjau

1. **Ringkasan** (`/app/ringkasan`): statistik hari ini, perlu ditinjau, anggota
   belum absen. Angka utama memakai serif 40 px, sisanya mono.
2. **Presensi** (`/app/presensi`): tabel harian dengan filter lokasi, divisi,
   status, dan Pita Periode.
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

### Platform admin

Platform admin masuk lewat halaman masuk yang sama di `/masuk` dan diarahkan ke
`/platform` setelah berhasil. Tidak ada halaman masuk terpisah. Bila pengguna
bukan platform admin membuka `/platform`, kembalikan 403.

1. **Daftar tenant** (`/platform/tenant`).
2. **Detail tenant** (`/platform/tenant/{tenant}`): status, paket, jumlah
   anggota. Tanpa data presensi atau selfie.
3. **Ubah status tenant**: aksi di halaman detail, bukan halaman terpisah.

## Copy

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

## Gerak

- Durasi 120 sampai 200 ms. Easing `ease-out`.
- Hanya properti `opacity` dan `transform`.
- Cap di hero boleh miring dari -2 ke 0 derajat saat hover, 200 ms.
- Garis bawah tautan boleh tumbuh dari kiri, 150 ms.
- Tidak ada animasi masuk berulang, parallax, atau spinner berputar.
- Hormati `prefers-reduced-motion`: semua transisi menjadi 0 ms.

## Aksesibilitas

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

## Peta

- Leaflet dengan tile OpenStreetMap.
- Tinggi peta 240 px di form lokasi, 320 px di layar absen.
- Peta tetap terang di mode gelap.
- Marker lokasi memakai pin cap, bukan ikon pustaka.
- Lingkaran radius memakai garis cap 1.5 px dengan isi 8 persen.
- Posisi pengguna memakai titik data dengan cincin surface.
- Selalu tampilkan teks jarak dan akurasi di bawah peta.
- Jangan memuat peta sebelum pengguna memberi izin lokasi. Tampilkan panel
  penjelasan dan tombol "Aktifkan lokasi".

## Checklist sebelum menandai layar selesai

- [ ] Satu tindakan utama yang jelas.
- [ ] Satu momen visual yang layak diingat, dan hanya satu.
- [ ] Tidak ada warna di luar token.
- [ ] Tidak ada radius, bayangan, atau gradien di luar aturan.
- [ ] Serif hanya untuk pernyataan dan angka fokus.
- [ ] Mono hanya untuk angka dan metadata.
- [ ] Teks bahasa Indonesia, tanpa emoji dan em dash.
- [ ] Kontras teks dan komponen lulus.
- [ ] Dapat dipakai dengan keyboard.
- [ ] Keadaan kosong, memuat, dan galat ada.
- [ ] Tampil baik di 360 px, 768 px, 1024 px, dan 1440 px.
- [ ] Mode gelap diperiksa: tidak ada kedipan tema, tombol tema ada di setiap
  layout, dan `aria-pressed` benar.
- [ ] Mark merek memakai varian yang kontras di kedua tema, tanpa bayangan
  putih sebagai pengganti varian.
- [ ] Tidak ada klaim atau angka palsu.
