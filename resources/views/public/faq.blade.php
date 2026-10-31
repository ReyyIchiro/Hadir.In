<x-layouts.public title="Pertanyaan - Hadir.In" description="Pertanyaan umum tentang presensi, lokasi, selfie, jadwal, izin, dan laporan Hadir.In.">
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

    {{-- FAQPage memakai $groups yang sama dengan yang dirender, jadi data
         terstruktur tidak pernah berbeda dari isi halaman. --}}
    @push('structured-data')
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'inLanguage' => 'id',
            'mainEntity' => collect($groups)->flatten(1)->map(fn (array $pair) => [
                '@type' => 'Question',
                'name' => $pair[0],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $pair[1],
                ],
            ])->values()->all(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endpush

    <x-public.page-header
        folio="01"
        label="Pertanyaan"
        title="Jawaban singkat, tanpa muter-muter."
        lede="Hal yang paling sering ditanyakan sebelum memakai Hadir.In. Bila jawabannya belum ada, admin organisasi adalah orang pertama yang perlu dihubungi."
    >
        <x-slot:aside>
            <dl class="space-y-4 text-[13px] leading-6">
                <div>
                    <dt class="label">Isi</dt>
                    <dd class="mt-1">{{ count($groups) }} kelompok, {{ array_sum(array_map('count', $groups)) }} pertanyaan</dd>
                </div>
                <div>
                    <dt class="label">Topik</dt>
                    <dd class="mt-1">Presensi, jadwal, izin, data</dd>
                </div>
                <div>
                    <dt class="label">Bila belum terjawab</dt>
                    <dd class="mt-1">Hubungi admin organisasi Anda</dd>
                </div>
            </dl>
        </x-slot:aside>

        <x-slot:actions>
            <a href="{{ route('public.terms') }}" class="btn btn-secondary">Ketentuan layanan</a>
            <a href="{{ route('register') }}" class="btn btn-primary btn-arrow">Mulai dengan organisasi</a>
        </x-slot:actions>
    </x-public.page-header>

    <section>
        <div class="container-page py-12 md:py-16">
            <div class="grid gap-6 lg:grid-cols-12 lg:gap-8">
                <x-public.toc :items="collect($groups)->map(fn ($items, $group) => ['id' => \Illuminate\Support\Str::slug($group), 'label' => $group])->values()->all()" label="Kelompok" />

                <div class="space-y-10 lg:col-span-8 lg:col-start-5">
                    @foreach ($groups as $group => $items)
                        <section id="{{ \Illuminate\Support\Str::slug($group) }}" class="scroll-mt-24">
                            <div class="flex items-baseline justify-between gap-4 border-b border-rule-strong pb-3">
                                <div class="flex items-baseline gap-3">
                                    <span class="folio text-stamp">{{ str_pad((string) ($loop->iteration), 2, '0', STR_PAD_LEFT) }}</span>
                                    <h2 class="font-display text-[24px] font-medium leading-[1.2] tracking-[-0.02em]">{{ $group }}</h2>
                                </div>
                                <span class="folio">{{ count($items) }} pertanyaan</span>
                            </div>

                            <div>
                                @foreach ($items as [$question, $answer])
                                    <details class="group border-b border-divider py-4">
                                        <summary class="flex cursor-pointer list-none items-start justify-between gap-4 text-[14px] font-medium marker:hidden">
                                            <span>{{ $question }}</span>
                                            <span class="mt-0.5 shrink-0 font-mono text-[14px] leading-none text-muted transition-transform group-open:rotate-45" aria-hidden="true">+</span>
                                        </summary>
                                        <p class="mt-2 max-w-[64ch] text-[14px] leading-7 text-muted">{{ $answer }}</p>
                                    </details>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="container-page py-12 md:py-20">
            <div class="grid gap-8 border-t border-rule-strong pt-8 lg:grid-cols-12 lg:items-end lg:gap-8">
                <div class="lg:col-span-8">
                    <p class="folio">02 / Bantuan</p>
                    <h2 class="display-md mt-4 max-w-[18ch]">Belum menemukan jawaban?</h2>
                    <p class="mt-3 max-w-[52ch] text-[14px] leading-7 text-muted">
                        Untuk pertanyaan tentang data dan kebijakan organisasi, hubungi admin organisasi Anda. Untuk hal teknis, gunakan alamat pada halaman ketentuan.
                    </p>
                </div>
                <div class="lg:col-span-4 lg:text-right">
                    <div class="flex flex-wrap gap-3 lg:justify-end">
                        <a href="{{ route('public.privacy') }}" class="btn btn-secondary">Kebijakan privasi</a>
                        <a href="{{ route('public.terms') }}" class="btn btn-primary btn-arrow">Baca ketentuan</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
