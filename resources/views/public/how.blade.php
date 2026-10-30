<x-layouts.public title="Cara kerja - Hadir.In" description="Alur Hadir.In dari pendaftaran organisasi sampai laporan bulanan.">
    <div class="mx-auto max-w-[820px] px-6 py-12 md:py-16">
        <a href="{{ route('home') }}" class="link inline-flex items-center gap-1 text-[13px]">
            <x-icon name="chevron-left" :size="14" />
            <span>Beranda</span>
        </a>

        <h1 class="mt-4 font-display text-[36px] font-semibold leading-[1.1] tracking-[-0.03em] md:text-[44px]">Cara kerja Hadir.In</h1>
        <p class="mt-3 max-w-[620px] text-[16px] leading-7 text-secondary">
            Hadir.In mengikuti urutan kerja harian: menyiapkan organisasi, menyusun jadwal, mencatat kehadiran, lalu menutup bulan.
        </p>

        <ol class="mt-10 space-y-8">
            @foreach([
                ['01', 'Siapkan organisasi', [
                    'Daftarkan organisasi dan pilih zona waktu. Zona waktu menentukan tanggal kerja dan batas jam absen.',
                    'Tambahkan lokasi kerja dengan titik pusat dan radius. Radius 50 meter cocok untuk kantor, sedangkan toko atau gudang bisa memakai radius lebih besar.',
                    'Atur jam kerja bawaan, toleransi keterlambatan, dan kebijakan selfie. Nilai ini dipakai bila anggota tidak memiliki jadwal khusus.',
                ]],
                ['02', 'Undang anggota dan susun jadwal', [
                    'Buat undangan berisi peran, lokasi utama, dan divisi. Tautan undangan berlaku 7 hari dan hanya dapat dipakai sekali.',
                    'Susun jadwal per anggota per tanggal, atau terapkan massal untuk rentang tanggal dan divisi tertentu.',
                    'Tandai hari libur organisasi agar hari tersebut tidak dihitung sebagai hari kerja.',
                ]],
                ['03', 'Anggota absen masuk dan pulang', [
                    'Anggota membuka halaman absen, memberi izin lokasi, lalu mengambil selfie. Sistem mencatat waktu, koordinat, akurasi, dan jarak ke lokasi terdekat.',
                    'Absen di luar radius ditolak. Anggota dapat mengajukan tinjauan bila yakin posisinya benar, misalnya karena GPS meleset.',
                    'Absen pulang dicatat setelah jam masuk. Bila lupa absen pulang, hari tersebut ditandai agar dapat dikoreksi.',
                ]],
                ['04', 'Tinjau pengecualian', [
                    'Peninjau menerima antrean pengajuan tinjauan presensi, koreksi data, dan izin.',
                    'Setiap keputusan tercatat pada data presensi, termasuk alasan dan waktu keputusan.',
                    'Cakupan peninjau mengikuti lokasi utama. Admin dan pemilik melihat seluruh organisasi.',
                ]],
                ['05', 'Tutup bulan dengan laporan', [
                    'Halaman laporan menampilkan ringkasan hari terjadwal, kehadiran, keterlambatan, izin, dan absen.',
                    'Unduh CSV untuk Excel Indonesia. Berkas memakai pemisah titik koma dan BOM UTF-8 agar karakter terbaca benar.',
                    'Laporan memakai data final. Koreksi setelah ekspor akan mengubah laporan berikutnya, bukan berkas yang sudah diunduh.',
                ]],
            ] as [$number, $title, $paragraphs])
                <li class="grid gap-3 border-t border-divider pt-6 sm:grid-cols-[64px_1fr]">
                    <p class="font-mono text-[13px] tabular-nums text-stamp">{{ $number }}</p>
                    <div>
                        <h2 class="font-display text-[20px] font-semibold tracking-[-0.02em]">{{ $title }}</h2>
                        <div class="mt-2 space-y-2">
                            @foreach($paragraphs as $paragraph)
                                <p class="text-[14px] leading-7 text-muted">{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    </div>
                </li>
            @endforeach
        </ol>

        <div class="mt-12 flex flex-wrap items-center gap-3 border-t border-divider pt-6">
            <a href="{{ route('register') }}" class="btn btn-primary">Mulai dengan organisasi</a>
            <a href="{{ route('public.faq') }}" class="btn btn-secondary">Baca pertanyaan</a>
        </div>
    </div>
</x-layouts.public>
