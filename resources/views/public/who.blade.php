<x-layouts.public title="Untuk siapa - Hadir.In" description="Contoh penggunaan Hadir.In untuk kantor, toko, dan tim lapangan.">
    <x-public.page-header
        folio="01"
        label="Untuk siapa"
        title="Tiga bentuk kerja, satu cara mencatat."
        lede="Hadir.In tidak dirancang untuk semua jenis perusahaan. Tiga contoh berikut menggambarkan bentuk kerja yang paling cocok, sekaligus batas yang belum tercakup di versi ini."
    >
        <x-slot:aside>
            <dl class="space-y-4 text-[13px] leading-6">
                <div>
                    <dt class="label">Cocok untuk</dt>
                    <dd class="mt-1">Kantor, toko, tim lapangan</dd>
                </div>
                <div>
                    <dt class="label">Titik absen</dt>
                    <dd class="mt-1">Satu atau beberapa lokasi</dd>
                </div>
                <div>
                    <dt class="label">Belum tercakup</dt>
                    <dd class="mt-1">Payroll, lembur, mesin absen</dd>
                </div>
            </dl>
        </x-slot:aside>

        <x-slot:actions>
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg btn-arrow w-full sm:w-auto">Mulai dengan organisasi</a>
            <a href="{{ route('public.how') }}" class="btn btn-secondary btn-lg w-full sm:w-auto">Lihat cara kerja</a>
        </x-slot:actions>
    </x-public.page-header>

    <section class="section">
        <div class="container-page">
            <div class="flex flex-wrap items-end justify-between gap-4 border-b border-rule-strong pb-3">
                <p class="folio">02 / Tiga contoh</p>
                <p class="text-[13px] text-muted">Bukan daftar fitur, melainkan bentuk kerja</p>
            </div>

            <div class="mt-8 space-y-10">
                @foreach ([
                    [
                        'Kantor dengan jam tetap',
                        'Satu lokasi utama, jam kerja 08.00 sampai 17.00, dan toleransi 10 menit. Anggota absen dari lobi kantor. Admin memeriksa keterlambatan dan mengekspor laporan bulanan untuk HR.',
                        ['Lokasi: kantor pusat, radius 60 meter.', 'Peninjau: satu orang per lantai atau divisi.', 'Laporan: rekap bulanan per divisi.'],
                    ],
                    [
                        'Toko dengan beberapa cabang',
                        'Setiap cabang punya titik dan radius sendiri. Peninjau cabang hanya melihat anggota dengan lokasi utama yang sama, sehingga keputusan tetap dekat dengan tempat kerja.',
                        ['Lokasi: satu titik per cabang.', 'Peran: peninjau per cabang, admin di pusat.', 'Laporan: filter per lokasi dan divisi.'],
                    ],
                    [
                        'Tim lapangan dan kunjungan',
                        'Anggota berpindah tempat setiap hari. Organisasi mendaftarkan beberapa lokasi kerja, lalu anggota absen di lokasi terdekat yang masih dalam radius.',
                        ['Lokasi: gudang, klien, atau titik proyek.', 'Kebijakan: izinkan absen tanpa jadwal bila perlu.', 'Catatan: jarak dan akurasi selalu terlihat.'],
                    ],
                ] as $index => $useCase)
                    <article class="grid gap-6 border-t border-divider pt-6 lg:grid-cols-12 lg:gap-8">
                        <p class="font-mono text-[13px] tabular-nums text-stamp lg:col-span-1">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</p>
                        <div class="lg:col-span-7">
                            <h2 class="font-display text-[26px] font-medium leading-[1.15] tracking-[-0.02em]">{{ $useCase[0] }}</h2>
                            <p class="mt-3 max-w-[58ch] text-[14px] leading-7 text-muted">{{ $useCase[1] }}</p>
                        </div>
                        <ul class="space-y-2 border-t border-divider pt-4 text-[13px] leading-6 text-secondary lg:col-span-4 lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0">
                            @foreach ($useCase[2] as $point)
                                <li class="flex gap-2">
                                    <span class="mt-[0.55em] h-px w-3 shrink-0 bg-rule-strong" aria-hidden="true"></span>
                                    <span>{{ $point }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <x-public.band folio="03 / Batas" title="Yang sengaja belum dikerjakan.">
        <div class="grid gap-px bg-white/15 sm:grid-cols-2">
            <div class="bg-ink p-5 md:p-6">
                <p class="folio">01 / Gaji</p>
                <p class="mt-3 text-[15px] font-medium">Payroll dan perhitungan gaji</p>
                <p class="mt-2 max-w-[34ch] text-[13px] leading-6 text-on-ink/70">Hadir.In mencatat kehadiran, bukan menghitung upah atau potongan.</p>
            </div>
            <div class="bg-ink p-5 md:p-6">
                <p class="folio">02 / Lembur</p>
                <p class="mt-3 text-[15px] font-medium">Lembur dan penjadwalan lintas hari</p>
                <p class="mt-2 max-w-[34ch] text-[13px] leading-6 text-on-ink/70">Jadwal mengikuti hari kerja. Shift yang melewati tengah malam belum didukung.</p>
            </div>
            <div class="bg-ink p-5 md:p-6">
                <p class="folio">03 / Perangkat</p>
                <p class="mt-3 text-[15px] font-medium">Aplikasi ponsel khusus</p>
                <p class="mt-2 max-w-[34ch] text-[13px] leading-6 text-on-ink/70">Hadir.In dibuka lewat peramban. Tidak ada unduhan dari toko aplikasi.</p>
            </div>
            <div class="bg-ink p-5 md:p-6">
                <p class="folio">04 / Integrasi</p>
                <p class="mt-3 text-[15px] font-medium">Mesin absen fisik</p>
                <p class="mt-2 max-w-[34ch] text-[13px] leading-6 text-on-ink/70">Data presensi berasal dari peramban anggota, bukan dari pemindai sidik jari.</p>
            </div>
        </div>
    </x-public.band>

    <section>
        <div class="container-page py-12 md:py-20">
            <div class="grid gap-8 border-t border-rule-strong pt-8 lg:grid-cols-12 lg:items-end lg:gap-8">
                <div class="lg:col-span-8">
                    <p class="folio">04 / Mulai</p>
                    <h2 class="display-md mt-4 max-w-[18ch]">Cocok dengan salah satu bentuk di atas?</h2>
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
