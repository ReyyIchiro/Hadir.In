<x-layouts.public title="Pertanyaan - Hadir.In" description="Pertanyaan umum tentang presensi, lokasi, selfie, jadwal, izin, dan laporan Hadir.In.">
    <div class="mx-auto max-w-[820px] px-6 py-12 md:py-16">
        <a href="{{ route('home') }}" class="link inline-flex items-center gap-1 text-[13px]">
            <x-icon name="chevron-left" :size="14" />
            <span>Beranda</span>
        </a>

        <h1 class="mt-4 font-display text-[36px] font-semibold leading-[1.1] tracking-[-0.03em] md:text-[44px]">Pertanyaan</h1>
        <p class="mt-3 max-w-[600px] text-[16px] leading-7 text-secondary">
            Jawaban singkat untuk hal yang paling sering ditanyakan sebelum memakai Hadir.In.
        </p>

        @php
            $groups = [
                'Mulai' => [
                    ['Bagaimana cara mendaftar?', 'Buka halaman daftar, isi nama, email, kata sandi, dan nama organisasi. Organisasi baru berstatus menunggu aktivasi. Pemilik dapat masuk dan melihat layar status, tetapi anggota belum dapat memakai aplikasi sampai organisasi diaktifkan.'],
                    ['Apakah ada biaya?', 'Versi ini belum memiliki alur pembayaran. Organisasi diaktifkan oleh admin platform. Hubungi kami bila ingin memakai Hadir.In untuk organisasi Anda.'],
                    ['Apakah butuh aplikasi ponsel?', 'Tidak. Hadir.In dibuka lewat peramban ponsel. Kamera dan lokasi diakses lewat izin peramban.'],
                ],
                'Presensi' => [
                    ['Bagaimana bila GPS tidak akurat?', 'Absen tetap dicatat dan diberi tanda akurasi rendah bila nilai akurasi melewati batas organisasi. Peninjau dapat memeriksa dan menerima kejadian tersebut.'],
                    ['Bagaimana bila berada di luar radius?', 'Absen ditolak dan tidak masuk ke data kehadiran. Anggota dapat mengajukan tinjauan. Peninjau memutuskan berdasarkan jarak, akurasi, dan alasan.'],
                    ['Apakah bisa absen dua kali?', 'Satu hari kerja memiliki satu absen masuk dan satu absen pulang. Percobaan kedua ditolak. Bila ada kesalahan, ajukan koreksi.'],
                    ['Apa yang terjadi bila lupa absen pulang?', 'Hari tersebut ditandai tanpa pulang. Anggota dapat mengajukan koreksi, dan peninjau dapat menerimanya.'],
                ],
                'Jadwal dan izin' => [
                    ['Apakah jadwal harus diisi setiap hari?', 'Tidak. Bila organisasi mengizinkan absen tanpa jadwal, anggota tetap dapat absen dan hari tersebut diberi tanda tanpa jadwal. Bila tidak diizinkan, anggota tanpa jadwal tidak dapat absen.'],
                    ['Berapa lama izin dapat diajukan?', 'Maksimal 30 hari per pengajuan. Cuti hanya dapat diajukan pada hari kerja terjadwal.'],
                    ['Siapa yang menyetujui izin?', 'Peninjau dan admin. Pemilik juga dapat menyetujui. Peninjau hanya melihat pengajuan anggota dalam cakupan lokasinya.'],
                ],
                'Data dan laporan' => [
                    ['Siapa yang dapat melihat selfie?', 'Anggota pemilik, admin, pemilik organisasi, dan peninjau dalam cakupan lokasi. Tautan selfie berlaku 5 menit dan tidak dapat diakses tanpa masuk.'],
                    ['Berapa lama selfie disimpan?', 'Bawaan 90 hari. Setelah itu berkas dihapus, tetapi catatan presensi tetap ada tanpa foto.'],
                    ['Format laporan apa yang tersedia?', 'CSV dengan pemisah titik koma dan BOM UTF-8, siap dibuka di Excel Indonesia. Tidak ada ekspor PDF di versi ini.'],
                    ['Apakah platform admin dapat melihat data presensi?', 'Tidak. Platform admin hanya melihat data administratif organisasi: nama, status, zona waktu, jumlah anggota aktif, dan jumlah undangan tertunda.'],
                ],
            ];
        @endphp

        <div class="mt-10 space-y-10">
            @foreach($groups as $group => $items)
                <section>
                    <h2 class="border-b border-divider pb-3 font-display text-[20px] font-semibold tracking-[-0.02em]">{{ $group }}</h2>
                    <div class="divide-y divide-divider">
                        @foreach($items as [$question, $answer])
                            <details class="group py-4">
                                <summary class="flex cursor-pointer items-start justify-between gap-4 text-[14px] font-medium">
                                    <span>{{ $question }}</span>
                                    <span class="mt-0.5 shrink-0 text-muted transition-transform group-open:rotate-45" aria-hidden="true">+</span>
                                </summary>
                                <p class="mt-2 max-w-[640px] text-[14px] leading-7 text-muted">{{ $answer }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <div class="mt-12 border-t border-divider pt-6">
            <p class="text-[14px] leading-7 text-muted">Belum menemukan jawaban? Baca ketentuan layanan atau hubungi admin organisasi Anda.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('public.terms') }}" class="btn btn-secondary btn-sm">Ketentuan layanan</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Mulai dengan organisasi</a>
            </div>
        </div>
    </div>
</x-layouts.public>
