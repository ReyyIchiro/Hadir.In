<x-layouts.public title="Untuk siapa - Hadir.In" description="Contoh penggunaan Hadir.In untuk kantor, toko, dan tim lapangan.">
    <div class="mx-auto max-w-[900px] px-6 py-12 md:py-16">
        <a href="{{ route('home') }}" class="link inline-flex items-center gap-1 text-[13px]">
            <x-icon name="chevron-left" :size="14" />
            <span>Beranda</span>
        </a>

        <h1 class="mt-4 max-w-[640px] font-display text-[36px] font-semibold leading-[1.1] tracking-[-0.03em] md:text-[44px]">
            Untuk organisasi yang mencatat kehadiran setiap hari.
        </h1>
        <p class="mt-3 max-w-[620px] text-[16px] leading-7 text-secondary">
            Hadir.In tidak dirancang untuk semua jenis perusahaan. Tiga contoh berikut menggambarkan bentuk kerja yang paling cocok.
        </p>

        <div class="mt-10 space-y-10">
            @foreach([
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
                <section class="grid gap-5 border-t border-divider pt-6 md:grid-cols-[1fr_280px]">
                    <div>
                        <p class="font-mono text-[12px] tabular-nums text-stamp">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</p>
                        <h2 class="mt-2 font-display text-[22px] font-semibold tracking-[-0.02em]">{{ $useCase[0] }}</h2>
                        <p class="mt-2 max-w-[540px] text-[14px] leading-7 text-muted">{{ $useCase[1] }}</p>
                    </div>
                    <ul class="space-y-2 border-l border-divider pl-5 text-[13px] leading-6 text-muted">
                        @foreach($useCase[2] as $point)
                            <li>{{ $point }}</li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>

        <div class="mt-12 border-t border-divider pt-6">
            <h2 class="font-display text-[20px] font-semibold tracking-[-0.02em]">Yang belum tercakup di versi ini</h2>
            <ul class="mt-3 grid gap-2 text-[14px] leading-6 text-muted sm:grid-cols-2">
                <li>Payroll dan perhitungan gaji.</li>
                <li>Lembur dan penjadwalan lintas hari.</li>
                <li>Aplikasi ponsel khusus.</li>
                <li>Integrasi mesin absen fisik.</li>
            </ul>
            <a href="{{ route('register') }}" class="btn btn-primary mt-6">Mulai dengan organisasi</a>
        </div>
    </div>
</x-layouts.public>
