<x-layouts.auth title="Atur ulang kata sandi">
    <div class="panel panel-pad">
        <p class="eyebrow">Atur ulang</p>
        <h1 class="mt-1 font-display text-[28px] font-semibold leading-[1.2] tracking-[-0.02em]">Buat kata sandi baru</h1>
        <p class="mt-2 text-[14px] leading-6 text-muted">Masukkan kata sandi baru untuk akun Anda.</p>

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <x-field name="email" label="Email" type="email" :value="old('email')" required autocomplete="email" />
            <x-field name="password" label="Kata sandi baru" type="password" required autocomplete="new-password" help="Minimal 8 karakter, mengandung huruf dan angka." />
            <x-field name="password_confirmation" label="Ulangi kata sandi baru" type="password" required autocomplete="new-password" />

            <button type="submit" class="btn btn-primary w-full">Simpan kata sandi</button>
        </form>
    </div>
</x-layouts.auth>
