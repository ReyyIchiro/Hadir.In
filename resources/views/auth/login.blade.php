<x-layouts.auth title="Masuk ke Hadir.In">
    <div class="panel p-6 md:p-8">
        <div class="flex items-center justify-between gap-4">
            <p class="label">Masuk</p>
            <span class="folio">01 / 02</span>
        </div>

        <h1 class="title-lg mt-3">Selamat datang kembali</h1>
        <p class="mt-2 max-w-[36ch] text-[14px] leading-6 text-muted">
            Masukkan email dan kata sandi akun Anda.
        </p>

        @if(session('status'))
            <p class="mt-5 border-l-2 border-present bg-tint px-3 py-2 text-[13px] leading-5">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
            @csrf

            <x-field name="email" label="Email" type="email" :value="old('email')" required autocomplete="email" autofocus />
            <x-field name="password" label="Kata sandi" type="password" required autocomplete="current-password" />

            <div class="flex items-center justify-between gap-3">
                <label class="choice">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    <span>Ingat saya</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-[13px] text-muted hover:text-text">Lupa kata sandi</a>
            </div>

            <button type="submit" class="btn btn-primary w-full">Masuk</button>
        </form>

        <p class="mt-6 border-t border-divider pt-4 text-[13px] text-muted">
            Belum punya organisasi?
            <a href="{{ route('register') }}" class="link text-text">Daftar di sini</a>.
        </p>
    </div>

    <p class="mt-6 text-center text-[12px] text-muted">
        Dengan masuk, Anda menyetujui
        <a href="{{ route('public.terms') }}" class="link">ketentuan layanan</a>.
    </p>
</x-layouts.auth>
