<div>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="page-title">Lokasi</h1>
            <p class="mt-1 max-w-2xl text-[14px] leading-6 text-muted">
                Setiap lokasi punya titik pusat dan radius. Absen di luar radius ditolak, kecuali masih di dalam buffer.
            </p>
        </div>
        <a href="{{ route('app.locations.create') }}" class="btn btn-primary btn-sm">Tambah lokasi</a>
    </div>

    @if($flash)
        <div class="mt-4 rounded-[4px] border border-divider bg-surface px-4 py-3 text-[13px] text-muted" role="status">{{ $flash }}</div>
    @endif

    <div class="mt-5">
        @if($locations->isEmpty())
            <x-panel>
                <x-empty-state title="Belum ada lokasi" description="Tambahkan lokasi kerja pertama agar anggota bisa absen dengan titik yang jelas.">
                    <x-slot:action>
                        <a href="{{ route('app.locations.create') }}" class="btn btn-primary btn-sm">Tambah lokasi</a>
                    </x-slot:action>
                </x-empty-state>
            </x-panel>
        @else
            <x-panel title="Daftar lokasi" description="Radius dalam meter. Buffer menambah toleransi di tepi radius.">
                <div class="table-scroll">
                    <table class="ledger">
                        <caption>Lokasi kerja</caption>
                        <thead>
                            <tr>
                                <th scope="col">Nama</th>
                                <th scope="col">Koordinat</th>
                                <th scope="col">Radius</th>
                                <th scope="col">Anggota</th>
                                <th scope="col">Status</th>
                                <th scope="col"><span class="sr-only">Tindakan</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($locations as $location)
                                <tr>
                                    <td>
                                        <a href="{{ route('app.locations.edit', $location) }}" class="link">{{ $location->name }}</a>
                                        @if($location->address)
                                            <p class="mt-0.5 max-w-[280px] truncate text-[12px] text-muted">{{ $location->address }}</p>
                                        @endif
                                    </td>
                                    <td class="font-mono text-[12px] tabular-nums text-muted">
                                        {{ number_format((float) $location->latitude, 5, ',', '.') }}, {{ number_format((float) $location->longitude, 5, ',', '.') }}
                                    </td>
                                    <td class="tabular-nums">{{ $location->radius_meters }} m</td>
                                    <td class="tabular-nums">{{ $location->memberships_count }}</td>
                                    <td>
                                        <span class="status-pill {{ $location->is_active ? 'status-active' : 'status-cancelled' }}">
                                            {{ $location->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <button type="button" wire:click="toggle({{ $location->id }})" class="link text-[12px]">
                                            {{ $location->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-panel>
        @endif
    </div>
</div>
