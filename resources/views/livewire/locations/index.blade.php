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
                <div class="panel-pad">
                    <x-empty-state title="Belum ada lokasi" description="Tambahkan lokasi kerja pertama agar anggota bisa absen dengan titik yang jelas.">
                        <x-slot:action>
                            <a href="{{ route('app.locations.create') }}" class="btn btn-primary btn-sm">Tambah lokasi</a>
                        </x-slot:action>
                    </x-empty-state>
                </div>
            </x-panel>
        @else
            <x-panel title="Daftar lokasi" description="Radius dalam meter. Buffer menambah toleransi di tepi radius.">
                <div class="table-scroll">
                    <table class="ledger">
                        <caption>Lokasi kerja</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="cell-grow">Nama</th>
                                <th scope="col" class="num-head cell-fit">Koordinat</th>
                                <th scope="col" class="num-head cell-fit">Radius</th>
                                <th scope="col" class="num-head cell-fit">Anggota</th>
                                <th scope="col" class="cell-status">Status</th>
                                <th scope="col" class="cell-action"><span class="sr-only">Tindakan</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($locations as $location)
                                <tr>
                                    <td class="cell-grow">
                                        <a href="{{ route('app.locations.edit', $location) }}" class="link">{{ $location->name }}</a>
                                        @if($location->address)
                                            <p class="mt-0.5 max-w-[280px] truncate text-[12px] text-muted">{{ $location->address }}</p>
                                        @endif
                                    </td>
                                    <td class="cell-num cell-fit cell-meta">
                                        {{ number_format((float) $location->latitude, 5, '.', '') }}, {{ number_format((float) $location->longitude, 5, '.', '') }}
                                    </td>
                                    <td class="cell-num cell-fit">{{ $location->radius_meters }} m</td>
                                    <td class="cell-num cell-fit">{{ $location->memberships_count }}</td>
                                    <td class="cell-status">
                                        <span class="status-pill {{ $location->is_active ? 'status-active' : 'status-cancelled' }}">
                                            {{ $location->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="cell-action">
                                        <button type="button" wire:click="toggle({{ $location->id }})" wire:loading.attr="disabled" wire:target="toggle" @if($location->is_active) data-confirm="Nonaktifkan lokasi ini?" data-confirm-detail="Anggota tidak dapat lagi absen di lokasi ini. Presensi yang sudah tercatat tetap tersimpan." data-confirm-action="Nonaktifkan" @endif class="link text-[12px]">
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
