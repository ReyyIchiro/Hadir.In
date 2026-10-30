# AGENTS.md

Panduan kerja untuk agen AI dan kontributor manusia di repositori Hadir.In. Baca
berkas ini sebelum mengubah kode, dokumen, atau antarmuka.

## Sumber kebenaran

Bila dua dokumen bertentangan, urutan prioritasnya:

1. `DESIGN.md` untuk bahasa visual, token, komponen, dan copy antarmuka
2. `docs/PRD.md` untuk perilaku produk dan aturan bisnis
3. `docs/DATABASE.md` untuk model data dan arti status
4. `docs/ARCHITECTURE.md` untuk batas modul dan pola implementasi
5. `docs/ROUTES.md` untuk peta halaman dan middleware
6. `docs/DECISIONS.md` untuk alasan keputusan
7. `docs/ROADMAP.md` untuk urutan pengerjaan

Dokumen yang lebih spesifik menang atas dokumen yang lebih umum. Setiap perubahan
perilaku harus memperbarui dokumen terkait pada commit yang sama.

## Aturan produk yang tidak boleh dilanggar

- Produk berbahasa Indonesia. Nama kelas, tabel, kolom, dan kode ditulis dalam
  bahasa Inggris. Label, pesan, dan copy ditulis dalam bahasa Indonesia.
- Tidak ada emoji di antarmuka, dokumen produk, atau pesan galat.
- Tidak ada em dash di copy antarmuka. Gunakan titik, koma, atau titik dua.
- Tidak ada klaim palsu, testimoni karangan, logo pelanggan karangan, atau angka
  pengguna yang tidak berasal dari data nyata.
- Satu anggota hanya boleh memiliki satu pasangan absen masuk dan absen pulang per
  hari kerja.
- Waktu disimpan dalam UTC. `work_date` adalah tanggal lokal pada zona waktu tenant.
- Setiap query data tenant wajib melewati scope tenant. Jangan pernah memakai
  `withoutGlobalScope` tanpa alasan yang ditulis di komentar dan diuji.
- Selfie disimpan di disk privat. Jangan pernah memakai `public` disk, URL publik
  permanen, atau menulis path selfie ke log.
- Platform admin tidak boleh melihat data presensi, selfie, atau detail anggota.
- Data presensi tidak dihapus. Koreksi menambah catatan dan mengubah hari presensi.
- Status izin, cuti, dan sakit yang disetujui memblokir absen masuk pada hari itu.

## Gaya kerja

- Kerjakan per milestone di `docs/ROADMAP.md`. Jangan membuka banyak modul sekaligus.
- Tulis pengujian untuk aturan bisnis sebelum atau bersama implementasinya.
- Jalankan `php artisan test` dan `npm run build` sebelum commit.
- Commit kecil dengan pesan imperatif: `feat: ...`, `fix: ...`, `docs: ...`,
  `test: ...`, `chore: ...`.
- Jangan menambahkan dependensi baru tanpa mencatat alasannya di
  `docs/DECISIONS.md`.
- Jangan mengubah token warna, font, atau radius di luar `DESIGN.md`.
- Jangan membuat komponen baru bila komponen Blade yang ada bisa dipakai ulang.

## Konvensi kode

- Controller tipis. Logika bisnis di kelas action atau service di
  `app/Actions` dan `app/Services`.
- Validasi request di Form Request atau di properti Livewire, bukan di controller.
- Otorisasi lewat policy dan gate. Jangan menaruh pengecekan peran di Blade.
- Model tenant memakai trait `BelongsToTenant` dan global scope tenant.
- Enum PHP untuk status, peran, tipe izin, dan tipe event. Nilai enum disimpan
  sebagai string di database.
- Semua aksi yang mengubah presensi menulis `attendance_events` atau
  `attendance_corrections`; jangan menghapus riwayat.
- Gunakan `DB::transaction()` untuk aksi yang menyentuh lebih dari satu tabel.
- Uang tidak dipakai di v1. Bila nanti ada, gunakan integer rupiah, bukan float.
- Tanggal ditampilkan dengan helper `format_tanggal()` dan `format_jam()`.
  Jangan memanggil `translatedFormat` langsung dari Blade.

## Perintah

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan test
./vendor/bin/pint
npm run build
php artisan schedule:work
php artisan queue:work
```

## Definisi selesai

Sebuah milestone selesai bila:

- Perilaku sesuai `docs/PRD.md` dan `DESIGN.md`.
- Ada pengujian otomatis untuk jalur bahagia dan jalur gagal yang penting.
- `php artisan test` lulus.
- `npm run build` lulus.
- Tidak ada data tenant yang bocor di pengujian.
- Dokumen terkait diperbarui.
- Commit sudah di-push ke `origin main`.

<!-- antislop:start -->
## antislop
For UI, copy, people, mobile layout, or code comments work, read `antislop.md` (core) and then the skill for the task:
- UI / visual: `skills/antislop-ui/SKILL.md`
- Copy & text: `skills/antislop-copywriting/SKILL.md`
- People: `skills/antislop-human/SKILL.md`
- Mobile / responsive: `skills/antislop-layoutmobile/SKILL.md`
- Code comments: `skills/antislop-code/SKILL.md`
Before starting, follow the core's "Two Usage Modes" section in strict order: explicit session instruction first, then global preference, then ask. A session instruction always wins. For a resolved mode, say `antislop active: <mode> (session override).` or `antislop active: <mode> (global preference).` once before presenting findings or making edits, using the actual mode and source. Acknowledging the user's request without naming the source does not replace this notice.
Only an explicit choice of antislop during or after selects a session mode. A request to review, audit, or avoid file edits does not select a mode; read the global preference in that case. Another skill's mode does not select antislop's mode.
If the mode is unresolved, ask during/after and end the response; wait for the answer before any UI review, planning, or concept. For read-only tasks, put the active-mode notice only at the start of the final answer, never in progress messages. For editing tasks, announce before the first edit and omit it from the final answer.
To update antislop later: download `antislop.md` again, or run `npx antislop-ai --update` if it was installed as skill folders.
<!-- antislop:end -->
