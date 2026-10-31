<x-layouts.public
    title="Hadir.In - Presensi dengan bukti waktu, tempat, dan wajah"
    description="Hadir.In mencatat kehadiran dengan waktu server, lokasi, dan selfie, lalu merapikan jadwal, izin, dan laporan bulanan untuk kantor, toko, dan tim lapangan."
>

{{-- Struktur organisasi dan situs hanya untuk dokumen akar. --}}
@push('structured-data')
    @include('partials.structured-data')
@endpush
    {{-- Pembuka: pernyataan, bukan janji kosong. --}}
    <section class="border-b border-divider">
        <div class="container-page">
            <div class="grid gap-12 py-12 md:py-16 lg:grid-cols-12 lg:gap-8 lg:py-24">
                <div class="lg:col-span-7 lg:pr-8">
                    <div class="flex items-center gap-3">
                        <span class="folio">01</span>
                        <span class="h-px w-8 bg-rule-strong" aria-hidden="true"></span>
                        <p class="label">Presensi dengan bukti</p>
                    </div>

                    <h1 class="display-xl mt-6 max-w-[13ch]">
                        Setiap kehadiran punya bukti.
                    </h1>

                    <p class="lede mt-6">
                        Waktu dicatat server. Lokasi diperiksa radius. Wajah disimpan
                        privat. Tiga bukti dalam satu baris yang bisa diaudit, bukan
                        catatan yang gampang hilang di grup pesan.
                    </p>

                    <div class="mt-8 flex flex-col items-stretch gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg btn-arrow w-full sm:w-auto">
                            Mulai dengan organisasi
                        </a>
                        <a href="{{ route('public.how') }}" class="btn btn-secondary btn-lg w-full sm:w-auto">
                            Lihat cara kerja
                        </a>
                    </div>

                    <p class="mt-6 max-w-[42ch] text-[13px] leading-6 text-muted">
                        Untuk kantor, toko dengan beberapa cabang, dan tim lapangan.
                        Tanpa unduhan aplikasi. Dibuka lewat peramban ponsel.
                    </p>
                </div>

                {{-- Bukti visual: artefak presensi, bukan mockup dashboard. --}}
                <div class="lg:col-span-5">
                    <div class="exhibit">
                        <div class="flex items-start justify-between gap-4 border-b border-divider pb-4">
                            <div>
                                <p class="data-sm text-muted">KAMIS / 29.10.2026</p>
                                <p class="mt-1 text-[15px] font-medium">Kantor Pusat</p>
                                <p class="data-sm mt-0.5 text-muted">08.00 - 17.00 / radius 50 m</p>
                            </div>
                            <span class="stamp stamp-lg shrink-0">Hadir</span>
                        </div>

                        <dl class="proof-strip mt-4">
                            <div class="proof-cell">
                                <dt class="proof-label">Masuk</dt>
                                <dd class="proof-time mt-1">07.56</dd>
                            </div>
                            <div class="proof-cell">
                                <dt class="proof-label">Jarak</dt>
                                <dd class="data-lg mt-1">12 m</dd>
                            </div>
                            <div class="proof-cell">
                                <dt class="proof-label">Akurasi</dt>
                                <dd class="data-lg mt-1">8 m</dd>
                            </div>
                        </dl>

                        <dl class="mt-4 space-y-2">
                            <div class="flex items-baseline justify-between gap-4 text-[13px]">
                                <dt class="text-muted">Pulang</dt>
                                <dd class="data-md">17.04</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4 text-[13px]">
                                <dt class="text-muted">Selfie</dt>
                                <dd class="text-[13px]">Tersimpan privat</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4 text-[13px]">
                                <dt class="text-muted">Dicatat</dt>
                                <dd class="data-md">server</dd>
                            </div>
                        </dl>

                        <p class="exhibit-caption">
                            <span><span class="exhibit-number">Bukti A</span> / contoh tampilan</span>
                            <span>Bukan data pelanggan</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Tiga bukti: inti produk, dinyatakan sebagai bagian, bukan kartu fitur. --}}
    <section class="ink-band">
        <div class="container-page py-12 md:py-16">
            <div class="grid gap-8 lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-4">
                    <p class="folio">02 / Tiga bukti</p>
                    <h2 class="display-md mt-4 max-w-[12ch]">
                        Bukan sekadar tombol hadir.
                    </h2>
                </div>

                <div class="grid gap-px bg-white/15 sm:grid-cols-3 lg:col-span-8">
                    <div class="bg-ink p-5 md:p-6">
                        <p class="folio">Waktu</p>
                        <p class="mt-3 font-mono text-[2rem] leading-none tracking-[-0.03em] tabular-nums">07.56</p>
                        <p class="mt-3 max-w-[28ch] text-[13px] leading-6 text-on-ink/70">
                            Diambil dari jam server organisasi. Bukan jam perangkat yang bisa diubah.
                        </p>
                    </div>
                    <div class="bg-ink p-5 md:p-6">
                        <p class="folio">Tempat</p>
                        <p class="mt-3 font-mono text-[2rem] leading-none tracking-[-0.03em] tabular-nums">12 m</p>
                        <p class="mt-3 max-w-[28ch] text-[13px] leading-6 text-on-ink/70">
                            Jarak ke titik kerja dihitung dari radius yang Anda tentukan sendiri.
                        </p>
                    </div>
                    <div class="bg-ink p-5 md:p-6">
                        <p class="folio">Wajah</p>
                        <p class="mt-3 font-mono text-[2rem] leading-none tracking-[-0.03em]">Privat</p>
                        <p class="mt-3 max-w-[28ch] text-[13px] leading-6 text-on-ink/70">
                            Selfie disimpan dengan akses bertanda tangan. Tidak ada tautan publik.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Masalah: catatan lapangan, bukan tiga kartu berikon. --}}
    <section class="section">
        <div class="container-page">
            <div class="grid gap-10 lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-4">
                    <p class="folio">03 / Masalah</p>
                    <h2 class="display-md mt-4 max-w-[13ch]">
                        Yang hilang dari absensi manual.
                    </h2>
                    <p class="mt-4 max-w-[34ch] text-[14px] leading-6 text-muted">
                        Bukan soal malas mencatat. Soal catatan yang tidak cukup
                        kuat untuk menjawab pertanyaan di akhir bulan.
                    </p>
                </div>

                <div class="lg:col-span-8">
                    <div class="definition-list border-t border-rule-strong">
                        <div class="definition-row">
                            <dt class="data-sm pt-1">01 / Kehadiran</dt>
                            <dd>
                                <p class="text-[15px] font-medium">Tidak jelas siapa yang benar-benar hadir.</p>
                                <p class="mt-1.5 max-w-[54ch] text-[14px] leading-6 text-muted">
                                    Catatan di grup pesan mudah terlewat. Titik lokasi dan waktu
                                    tidak tercatat, sehingga sulit ditelusuri saat ada sengketa.
                                </p>
                            </dd>
                        </div>
                        <div class="definition-row">
                            <dt class="data-sm pt-1">02 / Izin</dt>
                            <dd>
                                <p class="text-[15px] font-medium">Izin tercampur dengan obrolan harian.</p>
                                <p class="mt-1.5 max-w-[54ch] text-[14px] leading-6 text-muted">
                                    Pengajuan cuti dan sakit hilang di antara pesan lain.
                                    Persetujuan tidak tercatat rapi, dan saldo cuti jadi bahan debat.
                                </p>
                            </dd>
                        </div>
                        <div class="definition-row">
                            <dt class="data-sm pt-1">03 / Rekap</dt>
                            <dd>
                                <p class="text-[15px] font-medium">Rekap bulanan dikerjakan ulang.</p>
                                <p class="mt-1.5 max-w-[54ch] text-[14px] leading-6 text-muted">
                                    Data disalin dari beberapa tempat, lalu dihitung manual.
                                    Selisih angka baru ketahuan saat laporan sudah dikirim.
                                </p>
                            </dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Cara kerja: satu alur, empat langkah, garis yang menghubungkan. --}}
    <section class="section">
        <div class="container-page">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="folio">04 / Cara kerja</p>
                    <h2 class="display-md mt-4">Empat langkah, satu alur.</h2>
                </div>
                <a href="{{ route('public.how') }}" class="arrow-link">Baca cara kerja lengkap</a>
            </div>

            <ol class="mt-10 grid gap-px border-y border-rule-strong bg-divider sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['01', 'Atur organisasi', 'Tentukan zona waktu, jam kerja, lokasi, dan radius.'],
                    ['02', 'Susun jadwal', 'Tetapkan jadwal per anggota atau terapkan massal per divisi.'],
                    ['03', 'Anggota absen', 'Absen masuk dan pulang dengan waktu server, lokasi, dan selfie.'],
                    ['04', 'Tinjau dan ekspor', 'Selesaikan pengecualian, lalu unduh laporan bulanan.'],
                ] as [$number, $title, $body])
                    <li class="bg-paper px-5 py-6 md:px-6">
                        <p class="folio text-stamp">{{ $number }}</p>
                        <h3 class="mt-3 text-[15px] font-medium">{{ $title }}</h3>
                        <p class="mt-2 max-w-[30ch] text-[13px] leading-6 text-muted">{{ $body }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Untuk siapa: tiga situasi nyata, ditulis sebagai catatan. --}}
    <section class="section">
        <div class="container-page">
            <div class="grid gap-10 lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-4">
                    <p class="folio">05 / Untuk siapa</p>
                    <h2 class="display-md mt-4 max-w-[14ch]">
                        Dibuat untuk kerja yang berpindah tempat.
                    </h2>
                    <p class="mt-4 max-w-[32ch] text-[14px] leading-6 text-muted">
                        Satu organisasi, banyak titik kerja. Aturan yang sama,
                        catatan yang tetap rapi.
                    </p>
                    <a href="{{ route('public.who') }}" class="arrow-link mt-6">Lihat contoh penggunaan</a>
                </div>

                <div class="lg:col-span-8">
                    <div class="divide-y divide-divider border-y border-rule-strong">
                        @foreach([
                            ['Kantor dengan jam tetap', 'Satu lokasi utama, jam kerja jelas, dan rekap bulanan untuk HR.'],
                            ['Toko dengan beberapa cabang', 'Setiap cabang punya titik dan radius sendiri. Peninjau melihat cabangnya.'],
                            ['Tim lapangan', 'Absen dari lokasi kerja mana pun yang terdaftar, dengan catatan jarak.'],
                        ] as [$title, $body])
                            <div class="grid gap-2 py-5 sm:grid-cols-[12rem_1fr] sm:gap-6">
                                <h3 class="text-[15px] font-medium">{{ $title }}</h3>
                                <p class="max-w-[52ch] text-[14px] leading-6 text-muted">{{ $body }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Pertanyaan: dua kolom, jawaban ringkas. --}}
    <section class="section">
        <div class="container-page">
            <div class="grid gap-8 lg:grid-cols-12 lg:gap-8">
                <div class="lg:col-span-4">
                    <p class="folio">06 / Pertanyaan</p>
                    <h2 class="display-md mt-4">Yang sering ditanyakan.</h2>
                    <p class="mt-4 max-w-[30ch] text-[14px] leading-6 text-muted">
                        Bila ada yang belum terjawab, halaman pertanyaan memuat
                        daftar yang lebih lengkap.
                    </p>
                    <a href="{{ route('public.faq') }}" class="arrow-link mt-6">Semua pertanyaan</a>
                </div>

                <div class="lg:col-span-8">
                    <div class="border-t border-rule-strong">
                        @foreach([
                            ['Apakah selfie wajib?', 'Bisa diatur per organisasi. Bila diaktifkan, selfie diambil saat absen dan disimpan privat.'],
                            ['Bagaimana bila sinyal GPS buruk?', 'Absen tetap dicatat dengan tanda akurasi rendah. Peninjau dapat memeriksa dan menerima.'],
                            ['Apakah bisa dipakai di beberapa cabang?', 'Ya. Setiap cabang punya lokasi dan radius sendiri dalam satu organisasi.'],
                            ['Apakah ada aplikasi ponsel?', 'Hadir.In dibuka lewat peramban ponsel. Tidak ada unduhan aplikasi.'],
                        ] as [$question, $answer])
                            <details class="group border-b border-divider py-4">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-[14px] font-medium">
                                    <span>{{ $question }}</span>
                                    <span class="font-mono text-[16px] text-muted transition-transform group-open:rotate-45" aria-hidden="true">+</span>
                                </summary>
                                <p class="mt-2 max-w-[62ch] text-[13px] leading-6 text-muted">{{ $answer }}</p>
                            </details>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Penutup: satu kalimat, satu tindakan. --}}
    <section>
        <div class="container-page py-12 md:py-20">
            <div class="grid gap-8 border-t border-rule-strong pt-8 lg:grid-cols-12 lg:items-end lg:gap-8">
                <div class="lg:col-span-8">
                    <p class="folio">07 / Mulai</p>
                    <h2 class="display-md mt-4 max-w-[18ch]">
                        Mulai dari satu lokasi. Rapikan sisanya nanti.
                    </h2>
                </div>
                <div class="lg:col-span-4 lg:text-right">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg btn-arrow">
                        Buat organisasi
                    </a>
                    <p class="mt-3 text-[12px] text-muted">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="link">Masuk</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
