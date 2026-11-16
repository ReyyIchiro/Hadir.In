<div>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="page-title">Presensi</h1>
            <p class="mt-1 text-[14px] text-muted">
                {{ $canReview && $view === 'team' ? 'Daftar kehadiran anggota pada tanggal terpilih.' : 'Riwayat kehadiran Anda per bulan.' }}
            </p>
        </div>

        <div class="grid w-full grid-cols-2 gap-2 sm:flex sm:w-auto sm:flex-wrap">
            @if($canReview)
                <button type="button" wire:click="$set('view', '{{ $view === 'team' ? 'mine' : 'team' }}')" class="btn btn-secondary btn-sm col-span-2 sm:col-span-1">
                    {{ $view === 'team' ? 'Lihat presensi saya' : 'Lihat tim' }}
                </button>
            @endif
            <a href="{{ route('app.attendance.check-in') }}" class="btn btn-primary btn-sm">Absen masuk</a>
            <a href="{{ route('app.attendance.check-out') }}" class="btn btn-secondary btn-sm">Absen pulang</a>
        </div>
    </div>

    @if($canReview && $view === 'team')
        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <x-field name="date" label="Tanggal" type="date" wire:model.live="date" :help="$date ? \App\Support\Waktu::dateLong($date) : null" />
            <x-select name="locationFilter" label="Lokasi" wire:model.live.debounce.150ms="locationFilter" placeholder="Semua lokasi" :options="$locations->pluck('name', 'id')->all()" />
            <x-select name="divisionFilter" label="Divisi" wire:model.live.debounce.150ms="divisionFilter" placeholder="Semua divisi" :options="$divisions->pluck('name', 'id')->all()" />
            <x-select name="statusFilter" label="Status" wire:model.live.debounce.150ms="statusFilter" placeholder="Semua status" :options="$statuses" />
            <p class="col-span-full text-[12px] text-muted" style="display: none" wire:loading wire:target="date, locationFilter, divisionFilter, statusFilter, previousPage, nextPage">Memperbarui...</p>
        </div>

        <x-panel class="mt-4">
            @if($rows->isEmpty())
                <x-empty-state title="Tidak ada anggota pada filter ini" description="Ubah tanggal atau filter untuk melihat anggota lain." />
            @else
                <p class="table-scroll-hint">Geser untuk melihat kolom lain.</p>
                <div class="table-scroll">
                    <table class="ledger">
                        <caption>Daftar presensi anggota</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="cell-grow">Anggota</th>
                                <th scope="col">Divisi</th>
                                <th scope="col">Lokasi</th>
                                <th scope="col" class="num-head cell-fit">Jadwal</th>
                                <th scope="col" class="num-head cell-fit">Masuk</th>
                                <th scope="col" class="num-head cell-fit">Pulang</th>
                                <th scope="col" class="cell-status">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    <td class="cell-grow">
                                        @if($row['day'])
                                            <a href="{{ route('app.attendance.show', $row['day']) }}" class="link">{{ $row['membership']->user->name }}</a>
                                        @else
                                            {{ $row['membership']->user->name }}
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $row['membership']->division?->name ?? '--' }}</td>
                                    <td class="text-muted">{{ $row['membership']->primaryLocation?->name ?? '--' }}</td>
                                    <td class="cell-time cell-fit text-muted">
                                        @if($row['schedule'] && ! $row['schedule']->is_day_off)
                                            {{ substr((string) $row['schedule']->start_time, 0, 5) }}-{{ substr((string) $row['schedule']->end_time, 0, 5) }}
                                        @else
                                            --
                                        @endif
                                    </td>
                                    <td class="cell-time cell-fit">{{ \App\Support\Waktu::time($row['day']?->check_in_at) }}</td>
                                    <td class="cell-time cell-fit">{{ \App\Support\Waktu::time($row['day']?->check_out_at) }}</td>
                                    <td class="cell-status"><x-status :status="$row['status']" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-divider px-4 py-3 md:px-6">
                    <p class="text-[12px] text-muted">{{ $total }} anggota pada filter ini.</p>
                    @if($totalPages > 1)
                        <div class="flex items-center gap-2">
                            <span class="text-[12px] text-muted">Halaman {{ $currentPage }} dari {{ $totalPages }}</span>
                            <button type="button" class="btn btn-secondary btn-sm" wire:click="previousPage" wire:loading.attr="disabled" wire:target="previousPage, nextPage" @disabled($currentPage <= 1)>
                                Sebelumnya
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" wire:click="nextPage" wire:loading.attr="disabled" wire:target="previousPage, nextPage" @disabled($currentPage >= $totalPages)>
                                Berikutnya
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </x-panel>
    @else
        <div class="mt-5 flex items-center justify-between gap-3 border-y border-divider py-3">
            <button type="button" wire:click="previousMonth" wire:loading.attr="disabled" wire:target="previousMonth, nextMonth" class="btn btn-ghost btn-sm">
                <x-icon name="chevron-left" :size="16" />
                <span>Sebelumnya</span>
            </button>
            <p class="font-mono text-[13px] font-medium">
                {{ \App\Support\Waktu::monthLabel((int) substr($month, 5, 2)) }} {{ substr($month, 0, 4) }}
            </p>
            <button type="button" wire:click="nextMonth" wire:loading.attr="disabled" wire:target="previousMonth, nextMonth" class="btn btn-ghost btn-sm">
                <span>Berikutnya</span>
                <x-icon name="chevron-right" :size="16" />
            </button>
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-[1fr_280px]">
            <x-panel>
                @if($rows->isEmpty())
                    <x-empty-state title="Belum ada catatan bulan ini" description="Catatan muncul setelah Anda absen masuk atau ada izin disetujui." />
                @else
                    <div class="table-scroll">
                        <table class="ledger">
                            <caption>Riwayat presensi {{ \App\Support\Waktu::monthLabel((int) substr($month, 5, 2)) }} {{ substr($month, 0, 4) }}</caption>
                            <thead>
                                <tr>
                                    <th scope="col" class="cell-grow">Tanggal</th>
                                    <th scope="col" class="num-head cell-fit">Jadwal</th>
                                    <th scope="col" class="num-head cell-fit">Masuk</th>
                                    <th scope="col" class="num-head cell-fit">Pulang</th>
                                    <th scope="col" class="cell-status">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $row)
                                    <tr>
                                        <td class="cell-grow">
                                            <a href="{{ route('app.attendance.show', $row['day']) }}" class="link">
                                                {{ \App\Support\Waktu::dateShort($row['date']) }}
                                            </a>
                                            <span class="block text-[12px] text-muted">{{ \App\Support\Waktu::dayName($row['date']) }}</span>
                                        </td>
                                        <td class="cell-time cell-fit text-muted">
                                            @if($row['schedule'])
                                                {{ substr((string) $row['schedule']->start_time, 0, 5) }}-{{ substr((string) $row['schedule']->end_time, 0, 5) }}
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td class="cell-time cell-fit">{{ \App\Support\Waktu::time($row['day']->check_in_at) }}</td>
                                        <td class="cell-time cell-fit">{{ \App\Support\Waktu::time($row['day']->check_out_at) }}</td>
                                        <td class="cell-status"><x-status :status="$row['day']->displayStatus()" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-panel>

            <div class="space-y-4">
                <x-panel title="Ringkasan">
                    <div class="ledger-stats">
                        <div>
                            <p class="text-[12px] text-muted">Hadir</p>
                            <p class="stat-value mt-1">{{ $summary['hadir'] }}</p>
                        </div>
                        <div>
                            <p class="text-[12px] text-muted">Terlambat</p>
                            <p class="stat-value mt-1">{{ $summary['terlambat'] }}</p>
                        </div>
                        <div>
                            <p class="text-[12px] text-muted">Izin</p>
                            <p class="stat-value mt-1">{{ $summary['izin'] }}</p>
                        </div>
                        <div>
                            <p class="text-[12px] text-muted">Absen</p>
                            <p class="stat-value mt-1">{{ $summary['absen'] }}</p>
                        </div>
                    </div>
                </x-panel>

                <x-panel title="Catatan">
                    <div class="panel-pad">
                        <p class="text-[13px] leading-6 text-muted">
                            Jam dicatat server dalam zona waktu organisasi. Bila ada kekeliruan, buka detail hari lalu ajukan koreksi.
                        </p>
                    </div>
                </x-panel>
            </div>
        </div>
    @endif
</div>
