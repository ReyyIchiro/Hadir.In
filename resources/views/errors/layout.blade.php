<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('heading') - Hadir.In</title>
    @vite(['resources/css/app.css'])
    <script{!! csp_script_nonce() !!}>
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
    <main id="konten" class="mx-auto flex min-h-screen w-full max-w-[560px] flex-col justify-center px-6 py-16">
        <x-wordmark :href="route('home')" class="text-text" />

        <p class="mt-12 font-mono text-[12px] tracking-[0.08em] text-muted">@yield('code')</p>
        <h1 class="mt-3 font-display text-[32px] leading-[1.2] font-semibold tracking-[-0.02em]">
            @yield('heading')
        </h1>
        <p class="mt-4 max-w-[420px] text-[15px] leading-7 text-secondary">
            @yield('message')
        </p>

        <div class="mt-8 flex flex-wrap gap-2">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('home') }}" class="btn btn-secondary">Kembali</a>
            <a href="{{ route('home') }}" class="btn btn-primary">Ke beranda</a>
        </div>
    </main>
</body>
</html>
