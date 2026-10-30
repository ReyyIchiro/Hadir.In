<x-layouts.public title="Ketentuan layanan - Hadir.In" description="Ketentuan pemakaian Hadir.In untuk organisasi dan anggota.">
    @php
        $sections = [
            [
                'id' => 'pemakaian-layanan',
                'title' => 'Pemakaian layanan',
                'paragraphs' => [
                    'Hadir.In dipakai untuk mencatat kehadiran anggota dalam satu organisasi. Pemilik organisasi bertanggung jawab atas kebenaran data anggota, jadwal, dan pengaturan yang diberikan.',
                ],
            ],
            [
                'id' => 'akun-dan-keamanan',
                'title' => 'Akun dan keamanan',
                'paragraphs' => [
                    'Setiap pengguna memiliki satu akun dengan email unik. Kata sandi minimal 8 karakter dan harus berisi huruf serta angka. Pengguna bertanggung jawab menjaga kata sandi dan perangkat yang dipakai.',
                    'Undangan berlaku 7 hari, hanya dapat dipakai sekali, dan terikat pada satu alamat email. Jangan membagikan tautan undangan kepada orang lain.',
                ],
            ],
            [
                'id' => 'data-presensi',
                'title' => 'Data presensi',
                'paragraphs' => [
                    'Data presensi yang tercatat dianggap sah sampai ada koreksi yang disetujui. Koreksi tidak menghapus riwayat kejadian. Peninjau dan admin berwenang menerima atau menolak pengajuan sesuai kebijakan organisasi.',
                    'Manipulasi lokasi, selfie, atau waktu absen dapat menyebabkan penonaktifan keanggotaan oleh admin organisasi.',
                ],
            ],
            [
                'id' => 'ketersediaan',
                'title' => 'Ketersediaan',
                'paragraphs' => [
                    'Hadir.In berupaya tersedia setiap hari, tetapi tidak menjamin layanan bebas gangguan. Pemeliharaan terjadwal diumumkan melalui admin organisasi bila memengaruhi pemakaian.',
                ],
            ],
            [
                'id' => 'penangguhan-dan-penghentian',
                'title' => 'Penangguhan dan penghentian',
                'paragraphs' => [
                    'Organisasi dapat ditangguhkan bila melanggar ketentuan atau atas permintaan pemilik. Penangguhan menghentikan akses anggota tanpa menghapus data. Organisasi tidak dapat menghapus dirinya sendiri dari aplikasi.',
                ],
            ],
            [
                'id' => 'perubahan-ketentuan',
                'title' => 'Perubahan ketentuan',
                'paragraphs' => [
                    'Ketentuan dapat diperbarui seiring perubahan fitur. Versi terbaru selalu tersedia di halaman ini dengan tanggal berlaku yang diperbarui.',
                ],
            ],
        ];
    @endphp

    <x-public.page-header
        folio="01"
        label="Legal"
        title="Ketentuan layanan"
        lede="Aturan dasar pemakaian Hadir.In untuk organisasi dan anggota. Bagian ini mengatur akun, data presensi, ketersediaan, dan penghentian akses."
    >
        <x-slot:aside>
            <dl class="space-y-4 text-[13px] leading-6">
                <div>
                    <dt class="label">Berlaku</dt>
                    <dd class="mt-1">1 November 2026</dd>
                </div>
                <div>
                    <dt class="label">Cakupan</dt>
                    <dd class="mt-1">Organisasi dan anggota</dd>
                </div>
                <div>
                    <dt class="label">Undangan</dt>
                    <dd class="mt-1">7 hari, sekali pakai</dd>
                </div>
            </dl>
        </x-slot:aside>

        <x-slot:actions>
            <a href="{{ route('public.privacy') }}" class="btn btn-secondary">Baca kebijakan privasi</a>
            <a href="{{ route('public.faq') }}" class="btn btn-primary btn-arrow">Lihat pertanyaan umum</a>
        </x-slot:actions>
    </x-public.page-header>

    <x-public.document :sections="$sections" folio="02 / Isi" />
</x-layouts.public>
