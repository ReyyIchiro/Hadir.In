<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>{{ $title ?? 'Platform Hadir.In' }}</title>
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
        <div class="mx-auto flex h-14 max-w-[1200px] items-center gap-6 px-4 md:px-6">
            <a href="{{ route('platform.dashboard') }}" class="flex items-center gap-2 no-underline hover:no-underline">
                <span class="inline-block h-3 w-3 rounded-[2px] bg-stamp" aria-hidden="true"></span>
                <span class="font-display text-[15px] font-semibold tracking-[-0.02em]">Hadir.In</span>
                <span class="text-[12px] text-muted">Platform</span>
            </a>

            <nav class="flex items-center gap-4 text-[13px] text-muted" aria-label="Navigasi platform">
                <a href="{{ route('platform.dashboard') }}" class="hover:text-text {{ request()->routeIs('platform.dashboard') ? 'text-text' : '' }}">Ringkasan</a>
                <a href="{{ route('platform.tenants.index') }}" class="hover:text-text {{ request()->routeIs('platform.tenants.*') ? 'text-text' : '' }}">Organisasi</a>
            </nav>

            <form method="POST" action="{{ route('logout') }}" class="ml-auto">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm">Keluar</button>
            </form>
        </div>
    </header>

    <main id="konten" class="mx-auto w-full max-w-[1200px] px-4 py-6 md:px-6 md:py-8">
        {{ $slot }}
    </main>
</body>
</html>
