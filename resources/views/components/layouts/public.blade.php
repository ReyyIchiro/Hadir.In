<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.theme')
    @include('partials.brand', ['canonical' => url()->current()])
    <title>{{ $title ?? 'Hadir.In' }}</title>
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
    <meta name="description" content="{{ $description ?? 'Presensi dengan waktu server, lokasi, dan selfie untuk kantor, toko, dan tim lapangan di Indonesia.' }}">
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('structured-data')
</head>
<body class="bg-paper text-text antialiased">
    <a href="#konten" class="skip-link">Lewati ke konten</a>

    <header class="border-b border-divider">
        <div class="container-page">
            <div class="flex h-16 items-center justify-between gap-6">
                <x-wordmark :href="route('home')" />

                <nav class="hidden items-center gap-7 md:flex" aria-label="Navigasi utama">
                    <a href="{{ route('public.how') }}" class="nav-public" @if(request()->routeIs('public.how')) aria-current="page" @endif>Cara kerja</a>
                    <a href="{{ route('public.who') }}" class="nav-public" @if(request()->routeIs('public.who')) aria-current="page" @endif>Untuk siapa</a>
                    <a href="{{ route('public.faq') }}" class="nav-public" @if(request()->routeIs('public.faq')) aria-current="page" @endif>Pertanyaan</a>
                </nav>

                <div class="flex items-center gap-2">
                    <x-theme-toggle />
                    @auth
                        <a href="{{ route('app.home') }}" class="btn btn-primary btn-sm">Buka aplikasi</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Masuk</a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Daftar</a>
                    @endauth
                </div>
            </div>

            {{-- Di ponsel, tiga halaman utama tetap terlihat sebagai baris kedua. --}}
            <nav class="flex items-center gap-6 border-t border-divider py-2 md:hidden" aria-label="Navigasi halaman">
                <a href="{{ route('public.how') }}" class="nav-public" @if(request()->routeIs('public.how')) aria-current="page" @endif>Cara kerja</a>
                <a href="{{ route('public.who') }}" class="nav-public" @if(request()->routeIs('public.who')) aria-current="page" @endif>Untuk siapa</a>
                <a href="{{ route('public.faq') }}" class="nav-public" @if(request()->routeIs('public.faq')) aria-current="page" @endif>Pertanyaan</a>
            </nav>
        </div>
    </header>

    <main id="konten">
        {{ $slot }}
    </main>

    <footer class="border-t border-divider">
        <div class="container-page grid gap-6 pt-10 pb-24 md:grid-cols-12 md:gap-8 md:pb-20">
            <div class="md:col-span-6">
                <x-wordmark :href="route('home')" />
                <p class="mt-3 max-w-[36ch] text-[13px] leading-6 text-muted">
                    Buku absen resmi untuk operasi harian di Indonesia.
                    Waktu server, lokasi, dan selfie dalam satu catatan.
                </p>
            </div>

            <div class="md:col-span-3">
                <p class="label">Produk</p>
                <ul class="mt-3 space-y-2 text-[13px]">
                    <li><a href="{{ route('public.how') }}" class="link">Cara kerja</a></li>
                    <li><a href="{{ route('public.who') }}" class="link">Untuk siapa</a></li>
                    <li><a href="{{ route('public.faq') }}" class="link">Pertanyaan</a></li>
                    <li><a href="{{ route('login') }}" class="link">Masuk</a></li>
                </ul>
            </div>

            <div class="md:col-span-3">
                <p class="label">Legal</p>
                <ul class="mt-3 space-y-2 text-[13px]">
                    <li><a href="{{ route('public.privacy') }}" class="link">Privasi</a></li>
                    <li><a href="{{ route('public.terms') }}" class="link">Ketentuan</a></li>
                </ul>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-divider pt-4 md:col-span-12">
                <p class="folio">HADIR.IN / V2.0</p>
            </div>
        </div>
    </footer>

    <x-help-widget />

    @livewireScripts(['nonce' => config('csp_nonce', '')])
</body>
</html>
