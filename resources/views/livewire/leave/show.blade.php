<div>
    <a href="{{ route('app.leave.index') }}" class="link inline-flex items-center gap-1 text-[13px]">
        <x-icon name="chevron-left" :size="14" />
        <span>Kembali ke izin dan cuti</span>
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
        <div class="mt-4 rounded-[4px] border border-divider bg-surface px-4 py-3 text-[13px] leading-6 text-muted" role="status">{{ $flash }}</div>
    @endif

    <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_320px]">
        <x-panel title="Pengajuan">
            <div class="panel-pad space-y-4">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.06em] text-muted">Alasan</p>
                    <p class="mt-1 text-[14px] leading-6">{{ $leave->reason }}</p>
                </div>

                <dl class="grid gap-3 border-t border-divider pt-4 text-[13px] sm:grid-cols-2">
                    <div>
                        <dt class="text-muted">Diajukan</dt>
                        <dd class="mt-0.5">{{ \App\Support\Waktu::dateLong($leave->created_at) }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Jenis</dt>
                        <dd class="mt-0.5">{{ $leave->type->label() }}</dd>
                    </div>
                    @if($leave->reviewed_at)
                        <div>
                            <dt class="text-muted">Ditinjau</dt>
                            <dd class="mt-0.5">{{ \App\Support\Waktu::dateLong($leave->reviewed_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted">Peninjau</dt>
                            <dd class="mt-0.5">{{ $leave->reviewedBy?->name ?? '--' }}</dd>
                        </div>
                    @endif
                </dl>

                @if($leave->review_note)
                    <div class="border-l-2 border-divider pl-3">
                        <p class="text-[11px] uppercase tracking-[0.06em] text-muted">Catatan peninjau</p>
                        <p class="mt-1 text-[13px] leading-6">{{ $leave->review_note }}</p>
                    </div>
                @endif
            </div>
        </x-panel>

        @if($canReview && $leave->status === \App\Enums\LeaveStatus::Pending)
            <x-panel title="Keputusan">
                <form class="panel-pad space-y-3">
                    <x-field name="reviewNote" label="Catatan" type="textarea" rows="3" wire:model="reviewNote" help="Wajib diisi bila menolak." />
                    <div class="flex gap-2">
                        <button type="button" wire:click="approve" wire:loading.attr="disabled" wire:target="approve" class="btn btn-primary btn-sm flex-1">Setujui</button>
                        <button type="button" wire:click="reject" wire:loading.attr="disabled" wire:target="reject" data-confirm="Tolak pengajuan ini?" data-confirm-detail="Anggota akan melihat pengajuan ini ditolak beserta catatan Anda. Keputusan ini tidak dapat dibatalkan dari layar ini." data-confirm-action="Tolak pengajuan" class="btn btn-secondary btn-sm flex-1">Tolak</button>
                    </div>
                </form>
            </x-panel>
        @elseif($canCancel)
            <x-panel title="Batalkan pengajuan">
                <div class="panel-pad">
                    <p class="text-[13px] leading-6 text-muted">Pengajuan yang dibatalkan tidak lagi dihitung sebagai izin.</p>
                    <button type="button" wire:click="cancel" wire:loading.attr="disabled" wire:target="cancel" data-confirm="Batalkan pengajuan ini?" data-confirm-detail="Pengajuan tidak lagi dihitung sebagai izin. Anda dapat mengajukan ulang kapan saja." data-confirm-action="Batalkan pengajuan" class="btn btn-secondary btn-sm mt-3 w-full">Batalkan</button>
                </div>
            </x-panel>
        @endif
    </div>
</div>
