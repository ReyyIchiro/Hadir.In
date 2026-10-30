<div>
    <a href="{{ route('app.reviews.index', ['tab' => 'koreksi']) }}" class="link inline-flex items-center gap-1 text-[13px]">
        <x-icon name="chevron-left" :size="14" />
        <span>Kembali ke tinjauan</span>
    </a>

    <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="page-title">{{ $correction->user?->name ?? 'Anggota' }}</h1>
            <p class="mt-1 text-[14px] text-muted">
                {{ $day ? \App\Support\Waktu::dayName($day->work_date).', '.\App\Support\Waktu::dateLong($day->work_date) : '--' }}
            </p>
        </div>
        <x-status :status="$correction->status" />
    </div>

    @if($flash)
        <div class="mt-4 rounded-[4px] border border-absent/40 bg-absent/5 px-4 py-3 text-[13px] text-absent" role="status">{{ $flash }}</div>
    @endif

    <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_320px]">
        <div class="space-y-4">
            <x-panel title="Perbandingan jam">
                <div class="table-scroll">
                    <table class="ledger">
                        <caption>Jam lama dan usulan</caption>
                        <thead>
                            <tr>
                                <th scope="col">Kolom</th>
                                <th scope="col">Tercatat</th>
                                <th scope="col">Diusulkan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Jam masuk</td>
                                <td class="tabular-nums text-muted">{{ $originalCheckIn }}</td>
                                <td class="tabular-nums font-medium">{{ $requestedCheckIn ?? 'Tidak diubah' }}</td>
                            </tr>
                            <tr>
                                <td>Jam pulang</td>
                                <td class="tabular-nums text-muted">{{ $originalCheckOut }}</td>
                                <td class="tabular-nums font-medium">{{ $requestedCheckOut ?? 'Tidak diubah' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-panel>

            <x-panel title="Alasan anggota">
                <div class="panel-pad">
                    <p class="text-[14px] leading-6">{{ $correction->reason }}</p>
                    <p class="mt-2 text-[12px] text-muted">Diajukan oleh {{ $correction->requestedBy?->name ?? 'anggota' }} pada {{ \App\Support\Waktu::dateLong($correction->created_at) }}</p>
                </div>
            </x-panel>
        </div>

        @if($canDecide)
            <x-panel title="Keputusan">
                <div class="panel-pad space-y-3">
                    <x-field name="reviewNote" label="Catatan" type="textarea" rows="3" wire:model="reviewNote" help="Wajib diisi bila menolak." />
                    <div class="flex gap-2">
                        <button type="button" wire:click="approve" wire:loading.attr="disabled" wire:target="approve" class="btn btn-primary btn-sm flex-1">Setujui</button>
                        <button type="button" wire:click="reject" wire:loading.attr="disabled" wire:target="reject" class="btn btn-secondary btn-sm flex-1">Tolak</button>
                    </div>
                    <p class="text-[12px] leading-5 text-muted">Persetujuan mengubah catatan presensi dan menandainya sebagai dikoreksi.</p>
                </div>
            </x-panel>
        @endif
    </div>
</div>
