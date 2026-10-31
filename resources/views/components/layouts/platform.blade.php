<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.theme')
    @include('partials.brand')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>{{ $title ?? 'Platform Hadir.In' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-paper text-text antialiased">
    <a href="#konten" class="skip-link">Lewati ke konten</a>

    <header class="border-b border-divider">
        <div class="mx-auto flex h-14 max-w-[1200px] items-center gap-6 px-4 md:px-6">
            <x-wordmark :href="route('platform.dashboard')" label="Platform" class="text-[15px]" />

            <nav class="flex items-center gap-4 text-[13px] text-muted" aria-label="Navigasi platform">
                <a href="{{ route('platform.dashboard') }}" class="hover:text-text {{ request()->routeIs('platform.dashboard') ? 'text-text' : '' }}">Ringkasan</a>
                <a href="{{ route('platform.tenants.index') }}" class="hover:text-text {{ request()->routeIs('platform.tenants.*') ? 'text-text' : '' }}">Organisasi</a>
            </nav>

            <div class="ml-auto flex items-center gap-1">
                <x-theme-toggle />
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <main id="konten" class="mx-auto w-full max-w-[1200px] px-4 py-6 md:px-6 md:py-8">
        {{ $slot }}
    </main>
</body>
</html>
