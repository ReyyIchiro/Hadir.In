<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Hadir.In' }}</title>
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

    <div class="grid min-h-screen lg:grid-cols-[minmax(0,420px)_1fr]">
        <aside class="flex flex-col justify-between bg-ink px-8 py-10 text-paper lg:px-10 lg:py-12">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-paper no-underline hover:no-underline">
                <span class="inline-block h-3.5 w-3.5 rounded-[2px] bg-stamp" aria-hidden="true"></span>
                <span class="font-display text-[17px] font-semibold tracking-[-0.02em]">Hadir.In</span>
            </a>

            <div class="my-10 hidden lg:block">
                <span class="stamp">Buku absen</span>
                <p class="mt-8 max-w-[300px] font-display text-[28px] leading-[1.25] font-semibold tracking-[-0.02em]">
                    Kehadiran yang tercatat rapi, tanpa ribet.
                </p>
                <p class="mt-4 max-w-[280px] text-[14px] leading-6 text-paper/70">
                    Lokasi, selfie, jadwal, dan izin dalam satu catatan yang bisa dipercaya.
                </p>
            </div>

            <p class="text-[12px] text-paper/50">Dibuat untuk operasi harian di Indonesia.</p>
        </aside>

        <main id="konten" class="flex items-start justify-center px-6 py-10 lg:items-center lg:py-16">
            <div class="w-full max-w-[420px]">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
