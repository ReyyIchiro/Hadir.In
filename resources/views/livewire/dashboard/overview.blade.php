<div>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="eyebrow">{{ \App\Support\Waktu::dayName($today) }}, {{ \App\Support\Waktu::dateLong($today) }}</p>
            <h1 class="page-title mt-1">Ringkasan hari ini</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('app.attendance.index') }}" class="btn btn-secondary btn-sm">Daftar presensi</a>
            <a href="{{ route('app.reviews.index') }}" class="btn btn-primary btn-sm">Buka tinjauan</a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-2 border-y border-divider sm:grid-cols-5">
        @foreach([
            'scheduled' => 'Terjadwal',
            'checked_in' => 'Sudah masuk',
            'late' => 'Terlambat',
            'on_leave' => 'Izin disetujui',
            'absent' => 'Belum absen',
        ] as $key => $label)
            <div class="border-b border-divider px-4 py-4 sm:border-b-0 sm:border-r sm:last:border-r-0 md:px-5">
                <p class="text-[11px] uppercase tracking-[0.08em] text-muted">{{ $label }}</p>
                <p class="mt-1 font-mono text-[28px] tabular-nums leading-none">{{ $stats[$key] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-[1fr_360px]">
        <x-panel title="Antrean tinjauan" description="Permintaan yang menunggu keputusan Anda.">
            <div class="grid grid-cols-1 divide-y divide-divider sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                @foreach([
                    ['label' => 'Presensi', 'count' => $pending['reviews'], 'route' => route('app.reviews.index', ['tab' => 'presensi'])],
                    ['label' => 'Koreksi', 'count' => $pending['corrections'], 'route' => route('app.reviews.index', ['tab' => 'koreksi'])],
                    ['label' => 'Izin', 'count' => $pending['leave'], 'route' => route('app.reviews.index', ['tab' => 'izin'])],
                ] as $item)
                    <a href="{{ $item['route'] }}" class="block px-4 py-4 hover:bg-tint md:px-6">
                        <p class="text-[11px] uppercase tracking-[0.08em] text-muted">{{ $item['label'] }}</p>
                        <p class="mt-1 font-mono text-[26px] tabular-nums leading-none">{{ $item['count'] }}</p>
                        <p class="mt-2 text-[12px] text-muted">Lihat antrean</p>
                    </a>
                @endforeach
            </div>
        </x-panel>

        <x-panel title="Catatan terbaru" description="Absen masuk terakhir.">
            @if($recent->isEmpty())
                <x-empty-state title="Belum ada aktivitas" description="Aktivitas absen hari ini akan tampil di sini." />
            @else
                <ul class="divide-y divide-divider">
                    @foreach($recent as $day)
                        <li class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                            <div class="min-w-0">
                                <a href="{{ route('app.attendance.show', $day) }}" class="link block truncate text-[13px]">{{ $day->user?->name }}</a>
                                <p class="text-[11px] text-muted">{{ $day->location?->name ?? 'Tanpa lokasi' }}</p>
                            </div>
                            <span class="shrink-0 font-mono text-[12px] tabular-nums text-muted">{{ \App\Support\Waktu::time($day->check_in_at) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-panel>
    </div>
</div>
