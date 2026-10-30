<x-layouts.public title="Kebijakan privasi - Hadir.In" description="Bagaimana Hadir.In mengumpulkan, memakai, dan menyimpan data presensi.">
    <div class="mx-auto max-w-[760px] px-6 py-12 md:py-16">
        <a href="{{ route('home') }}" class="link inline-flex items-center gap-1 text-[13px]">
            <x-icon name="chevron-left" :size="14" />
            <span>Beranda</span>
        </a>

        <h1 class="mt-4 font-display text-[36px] font-semibold leading-[1.1] tracking-[-0.03em]">Kebijakan privasi</h1>
        <p class="mt-2 text-[13px] text-muted">Berlaku 1 November 2026.</p>

        <div class="mt-8 space-y-7 text-[14px] leading-7 text-secondary">
            <section>
                <h2 class="font-display text-[18px] font-semibold text-text">Data yang dikumpulkan</h2>
                <p class="mt-2">Hadir.In menyimpan data akun (nama, email, kata sandi terenkripsi), data keanggotaan (peran, lokasi utama, divisi), data jadwal, hari libur, pengajuan izin, serta data presensi.</p>
                <p class="mt-2">Data presensi mencakup waktu absen, koordinat lokasi, nilai akurasi GPS, jarak ke lokasi kerja, dan selfie bila kebijakan organisasi mewajibkannya.</p>
            </section>

            <section>
                <h2 class="font-display text-[18px] font-semibold text-text">Tujuan pemakaian</h2>
                <p class="mt-2">Data dipakai untuk mencatat kehadiran, menghitung keterlambatan dan ketidakhadiran, memproses izin, meninjau pengecualian, serta menyusun laporan bulanan bagi organisasi Anda.</p>
                <p class="mt-2">Hadir.In tidak memakai data presensi untuk iklan atau menjualnya kepada pihak ketiga.</p>
            </section>

            <section>
                <h2 class="font-display text-[18px] font-semibold text-text">Selfie</h2>
                <p class="mt-2">Selfie disimpan pada penyimpanan privat dan hanya dapat diakses melalui tautan bertanda tangan yang berlaku 5 menit. Tautan hanya dibuat untuk anggota pemilik, admin, pemilik organisasi, dan peninjau dalam cakupan lokasi.</p>
                <p class="mt-2">Berkas selfie dihapus setelah masa simpan yang ditetapkan organisasi, bawaan 90 hari. Catatan presensi tetap tersimpan tanpa foto.</p>
            </section>

            <section>
                <h2 class="font-display text-[18px] font-semibold text-text">Lokasi</h2>
                <p class="mt-2">Lokasi diambil hanya saat anggota menekan tombol absen. Hadir.In tidak melacak lokasi di latar belakang. Bila anggota menolak izin lokasi, absen tidak dapat dilakukan kecuali organisasi mengizinkan absen tanpa lokasi.</p>
            </section>

            <section>
                <h2 class="font-display text-[18px] font-semibold text-text">Akses dan pemisahan data</h2>
                <p class="mt-2">Setiap organisasi memiliki data terpisah. Anggota hanya melihat data dirinya. Peninjau melihat anggota dalam cakupan lokasinya. Admin dan pemilik melihat seluruh organisasi. Platform admin tidak dapat melihat data presensi atau selfie.</p>
            </section>

            <section>
                <h2 class="font-display text-[18px] font-semibold text-text">Masa simpan</h2>
                <p class="mt-2">Data presensi dan izin disimpan selama organisasi memakai Hadir.In. Organisasi dapat meminta penghapusan data melalui admin platform. Organisasi tidak dapat menghapus dirinya sendiri dari aplikasi.</p>
            </section>

            <section>
                <h2 class="font-display text-[18px] font-semibold text-text">Pertanyaan</h2>
                <p class="mt-2">Pertanyaan tentang data dapat disampaikan kepada admin organisasi Anda. Untuk hal teknis, hubungi pengelola Hadir.In melalui alamat yang tercantum pada halaman ketentuan.</p>
            </section>
        </div>
    </div>
</x-layouts.public>
