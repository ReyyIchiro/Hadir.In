<x-layouts.public title="Cara kerja - Hadir.In" description="Alur Hadir.In dari pendaftaran organisasi sampai laporan bulanan.">
    <x-public.page-header
        folio="01"
        label="Cara kerja"
        title="Lima langkah dari daftar sampai laporan."
        lede="Hadir.In mengikuti urutan kerja harian: menyiapkan organisasi, menyusun jadwal, mencatat kehadiran, lalu menutup bulan. Tidak ada langkah yang melompat."
    >
        <x-slot:aside>
            <dl class="space-y-4 text-[13px] leading-6">
                <div>
                    <dt class="label">Siklus</dt>
                    <dd class="mt-1">Harian, ditutup bulanan</dd>
                </div>
                <div>
                    <dt class="label">Perangkat</dt>
                    <dd class="mt-1">Peramban ponsel, tanpa unduhan</dd>
                </div>
                <div>
                    <dt class="label">Bukti</dt>
                    <dd class="mt-1">Waktu server, lokasi, selfie</dd>
                </div>
            </dl>
        </x-slot:aside>

        <x-slot:actions>
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg btn-arrow w-full sm:w-auto">Mulai dengan organisasi</a>
            <a href="{{ route('public.faq') }}" class="btn btn-secondary btn-lg w-full sm:w-auto">Baca pertanyaan</a>
        </x-slot:actions>
    </x-public.page-header>

    <x-public.band folio="02 / Urutan" title="Urutan yang sama, setiap hari.">
        <div class="grid gap-px bg-white/15 sm:grid-cols-3">
            <div class="bg-ink p-5 md:p-6">
                <p class="folio">01 / Jadwal</p>
                <p class="mt-3 max-w-[28ch] text-[13px] leading-6 text-on-ink/70">
                    Hari kerja dan jam ditentukan sebelum anggota menekan tombol. Jadwal menjadi dasar penilaian.
                </p>
            </div>
            <div class="bg-ink p-5 md:p-6">
                <p class="folio">02 / Absen</p>
                <p class="mt-3 max-w-[28ch] text-[13px] leading-6 text-on-ink/70">
                    Waktu, lokasi, dan selfie diperiksa saat itu juga. Di luar radius, absen ditolak.
                </p>
            </div>
            <div class="bg-ink p-5 md:p-6">
                <p class="folio">03 / Koreksi</p>
                <p class="mt-3 max-w-[28ch] text-[13px] leading-6 text-on-ink/70">
                    Pengecualian ditinjau, bukan dihapus. Riwayat keputusan tetap terlihat.
                </p>
            </div>
        </div>
    </x-public.band>

    <section class="section">
        <div class="container-page">
            <div class="flex flex-wrap items-end justify-between gap-4 border-b border-rule-strong pb-3">
                <p class="folio">03 / Alur kerja</p>
                <p class="text-[13px] text-muted">Lima langkah, satu siklus</p>
            </div>

            <ol class="mt-8 space-y-10">
                @foreach ([
                    ['01', 'Pemilik', 'Siapkan organisasi', [
                        'Daftarkan organisasi dan pilih zona waktu. Zona waktu menentukan tanggal kerja dan batas jam absen.',
                        'Tambahkan lokasi kerja dengan titik pusat dan radius. Radius 50 meter cocok untuk kantor, sedangkan toko atau gudang bisa memakai radius lebih besar.',
                        'Atur jam kerja bawaan, toleransi keterlambatan, dan kebijakan selfie. Nilai ini dipakai bila anggota tidak memiliki jadwal khusus.',
                    ]],
                    ['02', 'Admin', 'Undang anggota dan susun jadwal', [
                        'Buat undangan berisi peran, lokasi utama, dan divisi. Tautan undangan berlaku 7 hari dan hanya dapat dipakai sekali.',
                        'Susun jadwal per anggota per tanggal, atau terapkan massal untuk rentang tanggal dan divisi tertentu.',
                        'Tandai hari libur organisasi agar hari tersebut tidak dihitung sebagai hari kerja.',
                    ]],
                    ['03', 'Anggota', 'Absen masuk dan pulang', [
                        'Anggota membuka halaman absen, memberi izin lokasi, lalu mengambil selfie. Sistem mencatat waktu, koordinat, akurasi, dan jarak ke lokasi terdekat.',
                        'Absen di luar radius ditolak. Anggota dapat mengajukan tinjauan bila yakin posisinya benar, misalnya karena GPS meleset.',
                        'Absen pulang dicatat setelah jam masuk. Bila lupa absen pulang, hari tersebut ditandai agar dapat dikoreksi.',
                    ]],
                    ['04', 'Peninjau', 'Tinjau pengecualian', [
                        'Peninjau menerima antrean pengajuan tinjauan presensi, koreksi data, dan izin.',
                        'Setiap keputusan tercatat pada data presensi, termasuk alasan dan waktu keputusan.',
                        'Cakupan peninjau mengikuti lokasi utama. Admin dan pemilik melihat seluruh organisasi.',
                    ]],
                    ['05', 'Admin', 'Tutup bulan dengan laporan', [
                        'Halaman laporan menampilkan ringkasan hari terjadwal, kehadiran, keterlambatan, izin, dan absen.',
                        'Unduh CSV untuk Excel Indonesia. Berkas memakai pemisah titik koma dan BOM UTF-8 agar karakter terbaca benar.',
                        'Laporan memakai data final. Koreksi setelah ekspor akan mengubah laporan berikutnya, bukan berkas yang sudah diunduh.',
                    ]],
                ] as [$number, $role, $title, $paragraphs])
                    <li class="grid gap-4 border-t border-divider pt-6 sm:grid-cols-[64px_1fr] lg:grid-cols-[64px_1fr_120px] lg:gap-8">
                        <p class="font-mono text-[13px] tabular-nums text-stamp">{{ $number }}</p>
                        <div>
                            <h2 class="font-display text-[24px] font-medium leading-[1.2] tracking-[-0.02em]">{{ $title }}</h2>
                            <div class="mt-3 space-y-2">
                                @foreach ($paragraphs as $paragraph)
                                    <p class="max-w-[62ch] text-[14px] leading-7 text-muted">{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        </div>
                        <p class="folio lg:pt-1">{{ $role }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section>
        <div class="container-page py-12 md:py-20">
            <div class="grid gap-8 border-t border-rule-strong pt-8 lg:grid-cols-12 lg:items-end lg:gap-8">
                <div class="lg:col-span-8">
                    <p class="folio">04 / Mulai</p>
                    <h2 class="display-md mt-4 max-w-[18ch]">Mulai dari satu lokasi. Rapikan sisanya nanti.</h2>
                </div>
                <div class="lg:col-span-4 lg:text-right">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg btn-arrow">Buat organisasi</a>
                    <p class="mt-3 text-[12px] text-muted">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="link">Masuk</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
