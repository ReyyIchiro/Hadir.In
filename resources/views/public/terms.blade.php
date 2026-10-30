<x-layouts.public title="Ketentuan layanan - Hadir.In" description="Ketentuan pemakaian Hadir.In untuk organisasi dan anggota.">
    <div class="mx-auto max-w-[760px] px-6 py-12 md:py-16">
        <a href="{{ route('home') }}" class="link inline-flex items-center gap-1 text-[13px]">
            <x-icon name="chevron-left" :size="14" />
            <span>Beranda</span>
        </a>

        <h1 class="mt-4 font-display text-[36px] font-semibold leading-[1.1] tracking-[-0.03em]">Ketentuan layanan</h1>
        <p class="mt-2 text-[13px] text-muted">Berlaku 1 November 2026.</p>

        <div class="mt-8 space-y-7 text-[14px] leading-7 text-secondary">
            <section>
                <h2 class="font-display text-[18px] font-semibold text-text">Pemakaian layanan</h2>
                <p class="mt-2">Hadir.In dipakai untuk mencatat kehadiran anggota dalam satu organisasi. Pemilik organisasi bertanggung jawab atas kebenaran data anggota, jadwal, dan pengaturan yang diberikan.</p>
            </section>

            <section>
                <h2 class="font-display text-[18px] font-semibold text-text">Akun dan keamanan</h2>
                <p class="mt-2">Setiap pengguna memiliki satu akun dengan email unik. Kata sandi minimal 8 karakter dan harus berisi huruf serta angka. Pengguna bertanggung jawab menjaga kata sandi dan perangkat yang dipakai.</p>
                <p class="mt-2">Undangan berlaku 7 hari, hanya dapat dipakai sekali, dan terikat pada satu alamat email. Jangan membagikan tautan undangan kepada orang lain.</p>
            </section>

            <section>
                <h2 class="font-display text-[18px] font-semibold text-text">Data presensi</h2>
                <p class="mt-2">Data presensi yang tercatat dianggap sah sampai ada koreksi yang disetujui. Koreksi tidak menghapus riwayat kejadian. Peninjau dan admin berwenang menerima atau menolak pengajuan sesuai kebijakan organisasi.</p>
                <p class="mt-2">Manipulasi lokasi, selfie, atau waktu absen dapat menyebabkan penonaktifan keanggotaan oleh admin organisasi.</p>
            </section>

            <section>
                <h2 class="font-display text-[18px] font-semibold text-text">Ketersediaan</h2>
                <p class="mt-2">Hadir.In berupaya tersedia setiap hari, tetapi tidak menjamin layanan bebas gangguan. Pemeliharaan terjadwal diumumkan melalui admin organisasi bila memengaruhi pemakaian.</p>
            </section>

            <section>
                <h2 class="font-display text-[18px] font-semibold text-text">Penangguhan dan penghentian</h2>
                <p class="mt-2">Organisasi dapat ditangguhkan bila melanggar ketentuan atau atas permintaan pemilik. Penangguhan menghentikan akses anggota tanpa menghapus data. Organisasi tidak dapat menghapus dirinya sendiri dari aplikasi.</p>
            </section>

            <section>
                <h2 class="font-display text-[18px] font-semibold text-text">Perubahan ketentuan</h2>
                <p class="mt-2">Ketentuan dapat diperbarui seiring perubahan fitur. Versi terbaru selalu tersedia di halaman ini dengan tanggal berlaku yang diperbarui.</p>
            </section>
        </div>
    </div>
</x-layouts.public>
