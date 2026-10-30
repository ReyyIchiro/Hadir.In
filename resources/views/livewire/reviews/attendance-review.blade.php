<div>
    <a href="{{ route('app.reviews.index', ['tab' => 'presensi']) }}" class="link inline-flex items-center gap-1 text-[13px]">
        <x-icon name="chevron-left" :size="14" />
        <span>Kembali ke tinjauan</span>
    </a>

    <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="page-title">{{ $review->user?->name ?? 'Anggota' }}</h1>
            <p class="mt-1 text-[14px] text-muted">
                {{ $event ? \App\Support\Waktu::dayName($event->work_date).', '.\App\Support\Waktu::dateLong($event->work_date) : '--' }}
                · {{ $event?->type->label() ?? 'Absen' }}
            </p>
        </div>
        <x-status :status="$review->status" />
    </div>

    @if($flash)
        <div class="mt-4 rounded-[4px] border border-absent/40 bg-absent/5 px-4 py-3 text-[13px] text-absent" role="status">{{ $flash }}</div>
    @endif

    <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_320px]">
        <div class="space-y-4">
            <x-panel title="Kejadian">
                <dl class="divide-y divide-divider">
                    <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                        <dt class="text-[13px] text-muted">Waktu percobaan</dt>
                        <dd class="font-display text-[16px] font-semibold tabular-nums">{{ \App\Support\Waktu::time($event?->occurred_at) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                        <dt class="text-[13px] text-muted">Status</dt>
                        <dd>{{ $event?->status->label() ?? '--' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                        <dt class="text-[13px] text-muted">Lokasi</dt>
                        <dd class="text-right">{{ $event?->location?->name ?? 'Tanpa lokasi' }}</dd>
                    </div>
                    @if($event?->distance_meters !== null)
                        <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                            <dt class="text-[13px] text-muted">Jarak dari lokasi</dt>
                            <dd class="tabular-nums">{{ $event->distance_meters }} m</dd>
                        </div>
                    @endif
                    @if($event?->accuracy_meters !== null)
                        <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                            <dt class="text-[13px] text-muted">Akurasi GPS</dt>
                            <dd class="tabular-nums">{{ (int) round((float) $event->accuracy_meters) }} m</dd>
                        </div>
                    @endif
                    @if($event?->reason)
                        <div class="px-4 py-3 md:px-6">
                            <dt class="text-[13px] text-muted">Alasan sistem</dt>
                            <dd class="mt-1 text-[13px] leading-6">{{ \App\Support\Reasons::label($event->reason) }}</dd>
                        </div>
                    @endif
                </dl>
            </x-panel>

            <x-panel title="Alasan pengajuan">
                <div class="panel-pad">
                    <p class="text-[14px] leading-6">{{ $review->note ?: 'Anggota tidak menuliskan catatan tambahan.' }}</p>
                    <p class="mt-2 text-[12px] text-muted">Diajukan {{ \App\Support\Waktu::dateLong($review->created_at) }}</p>
                </div>
            </x-panel>
        </div>

        <div class="space-y-4">
            @if($selfie)
                <x-panel title="Selfie">
                    <div class="panel-pad">
                        <a href="{{ $selfie }}" target="_blank" rel="noopener">
                            <img src="{{ $selfie }}" alt="Selfie absen" class="w-full rounded-[2px] border border-divider object-cover">
                        </a>
                        <p class="mt-2 text-[12px] text-muted">Tautan berlaku 5 menit.</p>
                    </div>
                </x-panel>
            @endif

            @if($canDecide)
                <x-panel title="Keputusan">
                    <div class="panel-pad space-y-3">
                        <x-field name="reviewNote" label="Catatan" type="textarea" rows="3" wire:model="reviewNote" help="Wajib diisi bila menolak." />
                        <div class="flex gap-2">
                            <button type="button" wire:click="approve" wire:loading.attr="disabled" wire:target="approve" class="btn btn-primary btn-sm flex-1">Setujui</button>
                            <button type="button" wire:click="reject" wire:loading.attr="disabled" wire:target="reject" class="btn btn-secondary btn-sm flex-1">Tolak</button>
                        </div>
                        <p class="text-[12px] leading-5 text-muted">Persetujuan membuat catatan presensi untuk hari tersebut dengan tanda diterima manual.</p>
                    </div>
                </x-panel>
            @endif
        </div>
    </div>
</div>
