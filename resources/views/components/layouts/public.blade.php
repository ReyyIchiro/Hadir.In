<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Hadir.In' }}</title>
    <meta name="description" content="{{ $description ?? 'Presensi dengan lokasi dan selfie untuk kantor, toko, dan tim lapangan di Indonesia.' }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function () {
            var t = localStorage.getItem('hadir-theme');
            if (t === 'dark' || t === 'light') {
                document.documentElement.dataset.theme = t;
            } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.dataset.theme = 'dark';
            }
        })();
    </script>
</head>
<body class="bg-paper text-text antialiased">
    <a href="#konten" class="skip-link">Lewati ke konten</a>

    <header class="border-b border-divider">
        <div class="mx-auto flex h-16 max-w-[1120px] items-center justify-between gap-6 px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2 no-underline hover:no-underline">
                <span class="inline-block h-3.5 w-3.5 rounded-[2px] bg-stamp" aria-hidden="true"></span>
                <span class="font-display text-[17px] font-semibold tracking-[-0.02em]">Hadir.In</span>
            </a>

            <nav class="hidden items-center gap-6 text-[14px] text-muted md:flex" aria-label="Navigasi utama">
                <a href="{{ route('public.how') }}" class="hover:text-text">Cara kerja</a>
                <a href="{{ route('public.who') }}" class="hover:text-text">Untuk siapa</a>
                <a href="{{ route('public.faq') }}" class="hover:text-text">Pertanyaan</a>
            </nav>

            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('app.home') }}" class="btn btn-primary btn-sm">Buka aplikasi</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Daftar</a>
                @endauth
            </div>
        </div>
    </header>

    <main id="konten">
        {{ $slot }}
    </main>

    <footer class="border-t border-divider">
        <div class="mx-auto flex max-w-[1120px] flex-col gap-4 px-6 py-10 text-[13px] text-muted md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-2">
                <span class="inline-block h-3 w-3 rounded-[2px] bg-stamp" aria-hidden="true"></span>
                <span class="font-display text-[14px] font-semibold text-text">Hadir.In</span>
            </div>
            <p>Dibuat untuk operasi harian di Indonesia.</p>
            <nav class="flex flex-wrap items-center gap-4" aria-label="Navigasi kaki halaman">
                <a href="{{ route('login') }}" class="hover:text-text">Masuk</a>
                <a href="{{ route('register') }}" class="hover:text-text">Daftar</a>
                <a href="{{ route('public.privacy') }}" class="hover:text-text">Privasi</a>
                <a href="{{ route('public.terms') }}" class="hover:text-text">Ketentuan</a>
            </nav>
        </div>
    </footer>
</body>
</html>
