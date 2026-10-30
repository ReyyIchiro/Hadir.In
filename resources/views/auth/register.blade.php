<x-layouts.auth title="Daftar organisasi di Hadir.In">
    <div class="panel panel-pad">
        <p class="eyebrow">Daftar</p>
        <h1 class="mt-1 title-lg">Mulai dengan organisasi</h1>
        <p class="mt-2 text-[14px] leading-6 text-muted">
            Anda menjadi pemilik organisasi. Undang anggota setelah organisasi aktif.
        </p>

        <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
            @csrf

            <x-field name="name" label="Nama Anda" :value="old('name')" required autocomplete="name" autofocus />
            <x-field name="email" label="Email" type="email" :value="old('email')" required autocomplete="email" />
            <x-field name="organization" label="Nama organisasi" :value="old('organization')" required help="Contoh: PT Sinar Jaya atau Toko Kopi Pagi." />

            <x-select
                name="timezone"
                label="Zona waktu"
                :value="old('timezone', 'Asia/Jakarta')"
                :options="['Asia/Jakarta' => 'WIB, Asia/Jakarta (UTC+7)', 'Asia/Makassar' => 'WITA, Asia/Makassar (UTC+8)', 'Asia/Jayapura' => 'WIT, Asia/Jayapura (UTC+9)']"
                required
                help="Dipakai untuk jadwal dan laporan. Dapat diubah nanti di pengaturan."
            />

            <x-field name="password" label="Kata sandi" type="password" required autocomplete="new-password" help="Minimal 8 karakter, mengandung huruf dan angka." />
            <x-field name="password_confirmation" label="Ulangi kata sandi" type="password" required autocomplete="new-password" />

            <button type="submit" class="btn btn-primary w-full">Buat organisasi</button>
        </form>

        <p class="mt-6 border-t border-divider pt-4 text-[13px] text-muted">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="link text-text">Masuk</a>.
        </p>
    </div>
</x-layouts.auth>
