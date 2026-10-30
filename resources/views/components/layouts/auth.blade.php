<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.theme')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Hadir.In' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-paper text-text antialiased">
    <a href="#konten" class="skip-link">Lewati ke konten</a>

    <div class="grid lg:min-h-screen lg:grid-cols-[minmax(0,460px)_1fr]">
        <aside class="ink-band relative flex flex-col gap-6 overflow-hidden px-8 py-8 lg:justify-between lg:px-12 lg:py-12">
            {{-- Kolom tipis: satu-satunya dekorasi di panel ini. --}}
            <div class="pointer-events-none absolute inset-y-0 left-[72%] hidden w-px bg-white/10 lg:block" aria-hidden="true"></div>

            <a href="{{ route('home') }}" class="wordmark text-on-ink">
                <span class="wordmark-mark" aria-hidden="true"></span>
                <span>Hadir.In</span>
            </a>

            {{-- Di ponsel panel ini menjadi header pendek: satu pernyataan dan cap. --}}
            <div class="lg:my-10">
                <p class="folio hidden lg:block">CATATAN / 01</p>
                <p class="max-w-[18ch] font-display text-[1.75rem] leading-[1.08] tracking-[-0.03em] lg:mt-6 lg:max-w-[15ch] lg:text-[2.25rem] lg:leading-[1.1]">
                    Kehadiran yang tercatat rapi.
                </p>
                <p class="mt-4 hidden max-w-[30ch] text-[14px] leading-6 text-on-ink/70 lg:block">
                    Waktu server, lokasi, dan selfie dalam satu catatan
                    yang bisa diperiksa ulang.
                </p>

                <div class="mt-10 hidden border-t border-white/20 pt-5 lg:block">
                    <dl class="space-y-3">
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-[12px] text-on-ink/55">Waktu</dt>
                            <dd class="data-md text-on-ink">server</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-[12px] text-on-ink/55">Lokasi</dt>
                            <dd class="data-md text-on-ink">radius</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-[12px] text-on-ink/55">Wajah</dt>
                            <dd class="data-md text-on-ink">privat</dd>
                        </div>
                    </dl>
                </div>

                <span class="stamp stamp-quiet mt-5 text-on-ink/70 lg:mt-8">Buku absen</span>
            </div>

            <p class="hidden text-[12px] text-on-ink/50 lg:block">Dibuat untuk operasi harian di Indonesia.</p>
        </aside>

        <main id="konten" class="relative flex items-start justify-center px-6 py-10 lg:items-center lg:py-16">
            <div class="absolute right-4 top-4">
                <x-theme-toggle />
            </div>

            <div class="w-full max-w-[420px]">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
