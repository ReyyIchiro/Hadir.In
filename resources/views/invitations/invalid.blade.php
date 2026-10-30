<x-layouts.auth title="Undangan tidak berlaku">
    <div class="mx-auto w-full max-w-[440px]">
        <p class="eyebrow">Undangan</p>
        <h1 class="mt-2 font-display text-[28px] font-semibold leading-tight tracking-[-0.02em]">Undangan tidak berlaku</h1>
        <p class="mt-2 text-[14px] leading-6 text-muted">{{ $reason }}</p>
        <p class="mt-4 text-[14px] leading-6 text-muted">
            Minta admin organisasi mengirim undangan baru. Tautan lama tidak dapat dipakai ulang.
        </p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('login') }}" class="btn btn-secondary">Masuk</a>
            <a href="{{ route('home') }}" class="btn btn-ghost">Beranda</a>
        </div>
    </div>
</x-layouts.auth>
