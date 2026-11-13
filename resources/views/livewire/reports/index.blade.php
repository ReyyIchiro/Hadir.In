<div>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="page-title">Laporan bulanan</h1>
            <p class="mt-1 max-w-2xl text-[14px] leading-6 text-muted">
                Rekap kehadiran per anggota per hari kerja. Hari libur dan hari tanpa jadwal tidak dihitung.
            </p>
        </div>
        <a href="{{ $exportUrl }}" class="btn btn-secondary btn-sm">
            <x-icon name="download" :size="15" />
            <span>Unduh CSV</span>
        </a>
    </div>

    <x-panel class="mt-5">
        <div class="grid gap-3 border-b border-divider px-4 py-3 sm:grid-cols-3 md:px-6">
            <x-select name="month" label="Bulan" wire:model.live="month" :options="$monthOptions" />
            <x-select name="divisionId" label="Divisi" wire:model.live="divisionId" placeholder="Semua divisi" :options="$divisions->pluck('name', 'id')->all()" />
            <x-select name="locationId" label="Lokasi utama" wire:model.live="locationId" placeholder="Semua lokasi" :options="$locations->pluck('name', 'id')->all()" />
        </div>

        <div class="grid grid-cols-2 border-b border-divider sm:grid-cols-3 lg:grid-cols-6">
            @foreach([
                'scheduled' => 'Hari terjadwal',
                'present' => 'Hadir',
                'late' => 'Terlambat',
                'leave' => 'Izin, sakit, cuti',
                'absent' => 'Absen',
                'pending_review' => 'Perlu ditinjau',
            ] as $key => $label)
                <div class="border-b border-divider px-4 py-3 sm:border-b-0 sm:border-r sm:last:border-r-0 md:px-6">
                    <p class="text-[11px] uppercase tracking-[0.08em] text-muted">{{ $label }}</p>
                    <p class="mt-1 font-mono text-[22px] tabular-nums">{{ $summary[$key] }}</p>
                </div>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-divider px-4 py-2.5 md:px-6">
            <p class="text-[13px] text-muted">Periode {{ $monthLabel }}</p>
            <p class="text-[12px] text-muted">{{ $total }} baris</p>
        </div>

        @if(($summary['not_finalized'] ?? 0) > 0)
            <p class="border-b border-divider px-4 py-2 text-[12px] text-muted md:px-6">
                {{ $summary['not_finalized'] }} hari terjadwal belum difinalisasi, jadi belum dihitung sebagai absen.
                Hari berjalan baru difinalisasi empat jam setelah jam pulang.
            </p>
        @endif

        @if(empty($rows))
            <x-empty-state title="Belum ada data" description="Tidak ada jadwal pada periode dan filter ini. Coba bulan lain atau ubah filter." />
        @else
            <div class="table-scroll">
                <table class="ledger">
                    <caption>Laporan presensi {{ $monthLabel }}</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="cell-fit">Tanggal</th>
                            <th scope="col" class="cell-grow">Nama</th>
                            <th scope="col">Divisi</th>
                            <th scope="col" class="num-head cell-fit">Jadwal</th>
                            <th scope="col" class="num-head cell-fit">Masuk</th>
                            <th scope="col" class="num-head cell-fit">Pulang</th>
                            <th scope="col" class="cell-status">Status</th>
                            <th scope="col">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td class="cell-date cell-fit">
                                    {{ \App\Support\Waktu::dateShort($row['date']) }}
                                    <p class="text-[11px] text-muted">{{ $row['day_name'] }}</p>
                                </td>
                                <td class="cell-grow">
                                    @if($row['attendance_day_id'])
                                        <a href="{{ route('app.attendance.show', $row['attendance_day_id']) }}" class="link">{{ $row['name'] }}</a>
                                    @else
                                        {{ $row['name'] }}
                                    @endif
                                </td>
                                <td class="text-muted">{{ $row['division'] ?: '--' }}</td>
                                <td class="cell-time cell-fit cell-meta text-muted">{{ $row['schedule'] }}</td>
                                <td class="cell-time cell-fit">{{ \App\Support\Waktu::time($row['check_in'], '--:--') }}</td>
                                <td class="cell-time cell-fit">{{ \App\Support\Waktu::time($row['check_out'], '--:--') }}</td>
                                <td class="cell-status">
                                    <x-status :status="$row['status']" />
                                    @if(! empty($row['flags']))
                                        <p class="mt-1 text-[11px] leading-4 text-muted">
                                            @foreach($row['flags'] as $flag)
                                                {{ \App\Support\Flags::label($flag) }}@if(! $loop->last), @endif
                                            @endforeach
                                        </p>
                                    @endif
                                </td>
                                <td class="max-w-[220px] text-[12px] text-muted">{{ $row['note'] ?: '--' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($totalPages > 1)
                <div class="flex items-center justify-between gap-3 border-t border-divider px-4 py-3 md:px-6">
                    <p class="text-[12px] text-muted">Halaman {{ $page }} dari {{ $totalPages }}</p>
                    <div class="flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="previousPage" @disabled($page <= 1)>
                            Halaman sebelumnya
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="nextPage" @disabled($page >= $totalPages)>
                            Halaman berikutnya
                        </button>
                    </div>
                </div>
            @endif
        @endif
    </x-panel>
</div>
