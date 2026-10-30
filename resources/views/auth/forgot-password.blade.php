<x-layouts.auth title="Lupa kata sandi">
    <div class="panel panel-pad">
        <p class="eyebrow">Atur ulang</p>
        <h1 class="mt-1 font-display text-[28px] font-semibold leading-[1.2] tracking-[-0.02em]">Lupa kata sandi</h1>
        <p class="mt-2 text-[14px] leading-6 text-muted">
            Masukkan email akun Anda. Kami kirim tautan untuk membuat kata sandi baru.
        </p>

        @if(session('status'))
            <p class="mt-4 border-l-2 border-present bg-tint px-3 py-2 text-[13px] leading-5">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf
            <x-field name="email" label="Email" type="email" :value="old('email')" required autocomplete="email" autofocus />
            <button type="submit" class="btn btn-primary w-full">Kirim tautan</button>
        </form>

        <p class="mt-6 border-t border-divider pt-4 text-[13px] text-muted">
            Ingat kata sandi?
            <a href="{{ route('login') }}" class="link text-text">Kembali ke halaman masuk</a>.
        </p>
    </div>
</x-layouts.auth>
