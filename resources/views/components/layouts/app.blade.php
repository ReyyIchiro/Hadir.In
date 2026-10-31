<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.theme')
    @include('partials.brand')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>{{ $title ?? 'Hadir.In' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-paper text-text antialiased">
    <a href="#konten" class="skip-link">Lewati ke konten</a>

    @php
        $membership = $currentMembership ?? null;
        $role = $membership?->role;
        $canManageMembers = (bool) $role?->canManageMembers();
        $canManageSettings = (bool) $role?->canManageSettings();
        $canReview = (bool) $role?->canReview();

        $groups = [
            [
                'label' => null,
                'items' => array_values(array_filter([
                    $canReview ? ['label' => 'Ringkasan', 'route' => 'app.overview', 'icon' => 'home'] : null,
                    ['label' => 'Presensi', 'route' => 'app.attendance.index', 'icon' => 'clock'],
                    ['label' => 'Jadwal', 'route' => 'app.schedules.index', 'icon' => 'calendar'],
                    ['label' => 'Izin dan cuti', 'route' => 'app.leave.index', 'icon' => 'clipboard'],
                    $canReview ? ['label' => 'Tinjauan', 'route' => 'app.reviews.index', 'icon' => 'alert-triangle'] : null,
                    $canReview ? ['label' => 'Laporan', 'route' => 'app.reports.index', 'icon' => 'download'] : null,
                ])),
            ],
            [
                'label' => 'Kelola',
                'items' => array_values(array_filter([
                    $canManageMembers ? ['label' => 'Anggota', 'route' => 'app.members.index', 'icon' => 'users'] : null,
                    $canManageSettings ? ['label' => 'Lokasi', 'route' => 'app.locations.index', 'icon' => 'map-pin'] : null,
                    $canManageSettings ? ['label' => 'Divisi', 'route' => 'app.divisions.index', 'icon' => 'building'] : null,
                    $canManageSettings ? ['label' => 'Hari libur', 'route' => 'app.holidays.index', 'icon' => 'calendar'] : null,
                    $canManageSettings ? ['label' => 'Pengaturan', 'route' => 'app.settings.index', 'icon' => 'settings'] : null,
                ])),
            ],
        ];
    @endphp

    <div x-data="{ navOpen: false }" class="min-h-screen lg:grid lg:grid-cols-[240px_1fr]">
        <div
            x-cloak
            x-show="navOpen"
            x-transition.opacity
            class="fixed inset-0 z-30 bg-ink/50 lg:hidden"
            @click="navOpen = false"
            aria-hidden="true"
        ></div>

        <aside
            id="navigasi"
            class="fixed inset-y-0 left-0 z-40 flex w-[248px] flex-col overflow-y-auto border-r border-divider bg-paper px-3 py-4 transition-transform duration-150 ease-out lg:static lg:z-auto lg:w-auto lg:translate-x-0"
            :class="navOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            aria-label="Navigasi aplikasi"
        >
            <div class="flex items-center justify-between px-2 pb-4">
                <x-wordmark :href="route('app.home')" />
                <button type="button" class="btn btn-ghost btn-sm px-2 lg:hidden" @click="navOpen = false" aria-label="Tutup navigasi">
                    <x-icon name="x" :size="18" />
                </button>
            </div>

            {{-- Identitas organisasi: blok bergaris, bukan kartu. --}}
            <div class="mb-4 border-y border-divider px-2 py-3">
                <p class="label">Organisasi</p>
                <p class="mt-1 truncate text-[14px] font-medium">{{ $currentTenant->name ?? '' }}</p>
                <p class="mt-0.5 flex items-center gap-2 text-[12px] text-muted">
                    <span>{{ $role?->label() }}</span>
                    <span class="h-1 w-1 rounded-full bg-rule-strong" aria-hidden="true"></span>
                    <span class="data-sm">{{ $currentTenant->timezone ?? '' }}</span>
                </p>

                @if(($availableMemberships ?? collect())->count() > 1)
                    <details class="mt-2">
                        <summary class="cursor-pointer text-[12px] text-muted hover:text-text">Ganti organisasi</summary>
                        <ul class="mt-2 space-y-1">
                            @foreach($availableMemberships as $option)
                                @continue($option->tenant_id === ($currentTenant->id ?? null))
                                <li>
                                    <form method="POST" action="{{ route('app.tenants.switch', $option->tenant) }}">
                                        @csrf
                                        <button type="submit" class="w-full truncate rounded-[2px] px-1 py-1 text-left text-[12px] text-muted hover:bg-tint hover:text-text">
                                            {{ $option->tenant->name }}
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @endif
            </div>

            <nav class="space-y-5">
                @foreach($groups as $group)
                    @if(count($group['items']))
                        <div>
                            @if($group['label'])
                                <p class="label px-3 pb-1">{{ $group['label'] }}</p>
                            @endif
                            <ul class="space-y-0.5">
                                @foreach($group['items'] as $item)
                                    <li>
                                        <a
                                            href="{{ route($item['route']) }}"
                                            class="nav-link {{ request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])) ? 'active' : '' }}"
                                            @if(request()->routeIs($item['route'])) aria-current="page" @endif
                                        >
                                            <x-icon :name="$item['icon']" :size="17" class="shrink-0" />
                                            <span>{{ $item['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach
            </nav>

            <div class="mt-auto border-t border-divider pt-3">
                <a href="{{ route('app.profile') }}" class="nav-link">
                    <x-icon name="user" :size="17" class="shrink-0" />
                    <span>Profil</span>
                </a>
                <a href="{{ route('app.appearance') }}" class="nav-link">
                    <x-icon name="sun" :size="17" class="shrink-0" />
                    <span>Tampilan</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link w-full text-left">
                        <x-icon name="logout" :size="17" class="shrink-0" />
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-h-screen min-w-0 flex-col">
            <header class="sticky top-0 z-20 flex h-14 items-center gap-3 border-b border-divider bg-paper px-4 md:px-6">
                <button type="button" class="btn btn-ghost btn-sm px-2 lg:hidden" @click="navOpen = true" aria-label="Buka navigasi" aria-controls="navigasi">
                    <x-icon name="menu" :size="20" />
                </button>

                <p class="folio min-w-0 flex-1 truncate uppercase">
                    {{ $heading ?? $title ?? '' }}
                </p>

                <span class="hidden data-sm text-muted sm:block">{{ $currentTenant->timezone ?? '' }}</span>

                <x-theme-toggle />

                <span class="flex h-8 w-8 items-center justify-center rounded-[2px] border border-rule-strong bg-surface font-mono text-[12px] font-medium" aria-hidden="true">
                    {{ mb_strtoupper(mb_substr(auth()->user()->name ?? '?', 0, 1)) }}
                </span>
            </header>

            <main id="konten" class="flex-1 px-4 py-6 md:px-6 md:py-8">
                <div class="mx-auto w-full max-w-[1280px]">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
