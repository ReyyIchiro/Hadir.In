<div>
    <h1 class="page-title">Tinjauan</h1>
    <p class="mt-1 max-w-2xl text-[14px] leading-6 text-muted">
        Tinjau absen di luar radius, koreksi jam, dan pengajuan izin. Setiap keputusan tercatat.
    </p>

    <div class="mt-5 flex flex-wrap gap-1 border-b border-divider" role="tablist" aria-label="Jenis tinjauan">
        @foreach(['presensi' => 'Presensi', 'koreksi' => 'Koreksi', 'izin' => 'Izin dan cuti'] as $key => $label)
            <button
                type="button"
                role="tab"
                wire:click="setTab('{{ $key }}')"
                aria-selected="{{ $tab === $key ? 'true' : 'false' }}"
                class="relative -mb-px border-b-2 px-3 py-2 text-[13px] {{ $tab === $key ? 'border-stamp font-medium text-text' : 'border-transparent text-muted hover:text-text' }}"
            >
                {{ $label }}
                @if($counts[$key] > 0)
                    <span class="ml-1 inline-flex min-w-[18px] items-center justify-center rounded-[2px] bg-stamp-tint px-1 text-[11px] text-stamp tabular-nums">{{ $counts[$key] }}</span>
                @endif
            </button>
        @endforeach
    </div>

    @if($tab === 'presensi')
        @if($reviews->isEmpty())
            <x-empty-state title="Tidak ada absen menunggu tinjauan" description="Absen yang ditolak karena lokasi akan muncul di sini bila anggota mengajukan tinjauan." />
        @else
            <x-panel class="mt-4">
                <div class="table-scroll">
                    <table class="ledger">
                        <caption>Absen menunggu tinjauan</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="cell-grow">Anggota</th>
                                <th scope="col" class="cell-fit">Tanggal</th>
                                <th scope="col" class="cell-fit">Jenis</th>
                                <th scope="col">Lokasi</th>
                                <th scope="col">Alasan</th>
                                <th scope="col" class="cell-action"><span class="sr-only">Tindakan</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reviews as $review)
                                <tr>
                                    <td class="cell-grow">{{ $review->user?->name ?? '--' }}</td>
                                    <td class="cell-date cell-fit">{{ $review->event ? \App\Support\Waktu::dateShort($review->event->work_date) : '--' }}</td>
                                    <td class="cell-fit">{{ $review->event?->type->label() ?? '--' }}</td>
                                    <td class="text-muted">{{ $review->event?->location?->name ?? 'Tanpa lokasi' }}</td>
                                    <td class="max-w-[220px] truncate text-muted">{{ $review->note }}</td>
                                    <td class="cell-action">
                                        <a href="{{ route('app.reviews.attendance', $review) }}" class="link text-[12px]">Tinjau</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-panel>
        @endif
    @elseif($tab === 'koreksi')
        @if($corrections->isEmpty())
            <x-empty-state title="Tidak ada koreksi menunggu" description="Pengajuan koreksi jam dari anggota akan muncul di sini." />
        @else
            <x-panel class="mt-4">
                <div class="table-scroll">
                    <table class="ledger">
                        <caption>Koreksi menunggu tinjauan</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="cell-grow">Anggota</th>
                                <th scope="col" class="cell-fit">Tanggal kerja</th>
                                <th scope="col">Alasan</th>
                                <th scope="col">Diajukan</th>
                                <th scope="col" class="cell-action"><span class="sr-only">Tindakan</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($corrections as $correction)
                                <tr>
                                    <td class="cell-grow">{{ $correction->user?->name ?? '--' }}</td>
                                    <td class="cell-date cell-fit">{{ $correction->attendanceDay ? \App\Support\Waktu::dateShort($correction->attendanceDay->work_date) : '--' }}</td>
                                    <td class="max-w-[260px] truncate text-muted">{{ $correction->reason }}</td>
                                    <td class="cell-date cell-fit cell-meta">{{ \App\Support\Waktu::dateShort($correction->created_at) }}</td>
                                    <td class="cell-action">
                                        <a href="{{ route('app.reviews.correction', $correction) }}" class="link text-[12px]">Tinjau</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-panel>
        @endif
    @else
        @if($leaves->isEmpty())
            <x-empty-state title="Tidak ada izin menunggu" description="Pengajuan izin, sakit, dan cuti akan muncul di sini." />
        @else
            <x-panel class="mt-4">
                <div class="table-scroll">
                    <table class="ledger">
                        <caption>Izin menunggu tinjauan</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="cell-grow">Anggota</th>
                                <th scope="col" class="cell-fit">Jenis</th>
                                <th scope="col" class="cell-fit">Tanggal</th>
                                <th scope="col" class="num-head cell-fit">Hari</th>
                                <th scope="col">Diajukan</th>
                                <th scope="col" class="cell-action"><span class="sr-only">Tindakan</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaves as $leave)
                                <tr>
                                    <td class="cell-grow">{{ $leave->user?->name ?? '--' }}</td>
                                    <td class="cell-fit">{{ $leave->type->label() }}</td>
                                    <td class="cell-date cell-fit">{{ \App\Support\Waktu::dateShort($leave->start_date) }}</td>
                                    <td class="cell-num cell-fit">{{ $leave->durationInDays() }}</td>
                                    <td class="cell-date cell-fit cell-meta">{{ \App\Support\Waktu::dateShort($leave->created_at) }}</td>
                                    <td class="cell-action">
                                        <a href="{{ route('app.reviews.leave', $leave) }}" class="link text-[12px]">Tinjau</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-panel>
        @endif
    @endif
</div>
