<x-layouts.public title="Kebijakan privasi - Hadir.In" description="Bagaimana Hadir.In mengumpulkan, memakai, dan menyimpan data presensi.">
    @php
        $sections = [
            [
                'id' => 'data-yang-dikumpulkan',
                'title' => 'Data yang dikumpulkan',
                'paragraphs' => [
                    'Hadir.In menyimpan data akun (nama, email, kata sandi terenkripsi), data keanggotaan (peran, lokasi utama, divisi), data jadwal, hari libur, pengajuan izin, serta data presensi.',
                    'Data presensi mencakup waktu absen, koordinat lokasi, nilai akurasi GPS, jarak ke lokasi kerja, dan selfie bila kebijakan organisasi mewajibkannya.',
                ],
            ],
            [
                'id' => 'tujuan-pemakaian',
                'title' => 'Tujuan pemakaian',
                'paragraphs' => [
                    'Data dipakai untuk mencatat kehadiran, menghitung keterlambatan dan ketidakhadiran, memproses izin, meninjau pengecualian, serta menyusun laporan bulanan bagi organisasi Anda.',
                    'Hadir.In tidak memakai data presensi untuk iklan atau menjualnya kepada pihak ketiga.',
                ],
            ],
            [
                'id' => 'selfie',
                'title' => 'Selfie',
                'paragraphs' => [
                    'Selfie disimpan pada penyimpanan privat dan hanya dapat diakses melalui tautan bertanda tangan yang berlaku 5 menit. Tautan hanya dibuat untuk anggota pemilik, admin, pemilik organisasi, dan peninjau dalam cakupan lokasi.',
                    'Berkas selfie dihapus setelah masa simpan yang ditetapkan organisasi, bawaan 90 hari. Catatan presensi tetap tersimpan tanpa foto.',
                ],
            ],
            [
                'id' => 'lokasi',
                'title' => 'Lokasi',
                'paragraphs' => [
                    'Lokasi diambil hanya saat anggota menekan tombol absen. Hadir.In tidak melacak lokasi di latar belakang. Bila anggota menolak izin lokasi, absen tidak dapat dilakukan kecuali organisasi mengizinkan absen tanpa lokasi.',
                ],
            ],
            [
                'id' => 'akses-dan-pemisahan-data',
                'title' => 'Akses dan pemisahan data',
                'paragraphs' => [
                    'Setiap organisasi memiliki data terpisah. Anggota hanya melihat data dirinya. Peninjau melihat anggota dalam cakupan lokasinya. Admin dan pemilik melihat seluruh organisasi. Platform admin tidak dapat melihat data presensi atau selfie.',
                ],
            ],
            [
                'id' => 'masa-simpan',
                'title' => 'Masa simpan',
                'paragraphs' => [
                    'Data presensi dan izin disimpan selama organisasi memakai Hadir.In. Organisasi dapat meminta penghapusan data melalui admin platform. Organisasi tidak dapat menghapus dirinya sendiri dari aplikasi.',
                ],
            ],
            [
                'id' => 'pertanyaan',
                'title' => 'Pertanyaan',
                'paragraphs' => [
                    'Pertanyaan tentang data dapat disampaikan kepada admin organisasi Anda. Untuk hal teknis, hubungi pengelola Hadir.In melalui alamat yang tercantum pada halaman ketentuan.',
                ],
            ],
        ];
    @endphp

    <x-public.page-header
        folio="01"
        label="Legal"
        title="Kebijakan privasi"
        lede="Data presensi menyangkut orang dan tempat. Halaman ini menjelaskan data apa yang dicatat, untuk apa dipakai, dan siapa yang dapat melihatnya."
    >
        <x-slot:aside>
            <dl class="space-y-4 text-[13px] leading-6">
                <div>
                    <dt class="label">Berlaku</dt>
                    <dd class="mt-1">1 November 2026</dd>
                </div>
                <div>
                    <dt class="label">Cakupan</dt>
                    <dd class="mt-1">Akun, presensi, selfie, lokasi</dd>
                </div>
                <div>
                    <dt class="label">Pertanyaan</dt>
                    <dd class="mt-1">Admin organisasi Anda</dd>
                </div>
            </dl>
        </x-slot:aside>

        <x-slot:actions>
            <a href="{{ route('public.terms') }}" class="btn btn-secondary">Baca ketentuan layanan</a>
            <a href="{{ route('public.faq') }}" class="btn btn-primary btn-arrow">Lihat pertanyaan umum</a>
        </x-slot:actions>
    </x-public.page-header>

    <x-public.document :sections="$sections" folio="02 / Isi" />
</x-layouts.public>
