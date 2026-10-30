<div>
    <a href="{{ route('app.reviews.index', ['tab' => 'izin']) }}" class="link inline-flex items-center gap-1 text-[13px]">
        <x-icon name="chevron-left" :size="14" />
        <span>Kembali ke tinjauan</span>
    </a>

    <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="page-title">{{ $leave->type->label() }} · {{ $leave->user?->name ?? 'Anggota' }}</h1>
            <p class="mt-1 text-[14px] text-muted">
                {{ \App\Support\Waktu::dateLong($leave->start_date) }}
                @if(! $leave->start_date->equalTo($leave->end_date))
                    sampai {{ \App\Support\Waktu::dateLong($leave->end_date) }}
                @endif
                · {{ $leave->durationInDays() }} hari
            </p>
        </div>
        <x-status :status="$leave->status" />
    </div>

    @if($flash)
        <div class="mt-4 rounded-[4px] border border-divider bg-surface px-4 py-3 text-[13px] text-muted" role="status">{{ $flash }}</div>
    @endif

    <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_320px]">
        <x-panel title="Pengajuan">
            <div class="panel-pad space-y-4">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.06em] text-muted">Alasan</p>
                    <p class="mt-1 text-[14px] leading-6">{{ $leave->reason }}</p>
                </div>
                <p class="border-t border-divider pt-4 text-[12px] text-muted">Diajukan {{ \App\Support\Waktu::dateLong($leave->created_at) }}</p>
            </div>
        </x-panel>

        @if($canDecide)
            <x-panel title="Keputusan">
                <div class="panel-pad space-y-3">
                    <x-field name="reviewNote" label="Catatan" type="textarea" rows="3" wire:model="reviewNote" help="Wajib diisi bila menolak." />
                    <div class="flex gap-2">
                        <button type="button" wire:click="approve" wire:loading.attr="disabled" wire:target="approve" class="btn btn-primary btn-sm flex-1">Setujui</button>
                        <button type="button" wire:click="reject" wire:loading.attr="disabled" wire:target="reject" class="btn btn-secondary btn-sm flex-1">Tolak</button>
                    </div>
                    <p class="text-[12px] leading-5 text-muted">Persetujuan menandai hari kerja terkait sebagai izin, sakit, atau cuti.</p>
                </div>
            </x-panel>
        @endif
    </div>
</div>
