<div>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('app.attendance.index') }}" class="link inline-flex items-center gap-1 text-[13px]">
                <x-icon name="chevron-left" :size="14" />
                <span>Kembali ke presensi</span>
            </a>
            <h1 class="page-title mt-2">{{ $day->user->name }}</h1>
            <p class="mt-1 text-[14px] text-muted">{{ \App\Support\Waktu::dayName($day->work_date) }}, {{ \App\Support\Waktu::dateLong($day->work_date) }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-status :status="$day->displayStatus()" />
            @if($day->is_corrected)
                <span class="text-[12px] text-muted">Dikoreksi</span>
            @endif
        </div>
    </div>

    @if($flash)
        <div class="mt-4 rounded-[4px] border border-divider bg-surface px-4 py-3 text-[13px] leading-6 text-muted" role="status">{{ $flash }}</div>
    @endif

    <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_320px]">
        <div class="space-y-4">
            <x-panel title="Catatan hari ini">
                <dl class="divide-y divide-divider">
                    <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                        <dt class="text-[13px] text-muted">Jadwal</dt>
                        <dd class="tabular-nums">
                            @if($day->schedule)
                                {{ substr((string) $day->schedule->start_time, 0, 5) }}-{{ substr((string) $day->schedule->end_time, 0, 5) }}
                            @else
                                Mengikuti jam kerja organisasi
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                        <dt class="text-[13px] text-muted">Absen masuk</dt>
                        <dd class="font-display text-[18px] font-semibold tabular-nums">{{ \App\Support\Waktu::time($day->check_in_at) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                        <dt class="text-[13px] text-muted">Absen pulang</dt>
                        <dd class="font-display text-[18px] font-semibold tabular-nums">{{ \App\Support\Waktu::time($day->check_out_at) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                        <dt class="text-[13px] text-muted">Terlambat</dt>
                        <dd class="tabular-nums">{{ $day->late_minutes }} menit</dd>
                    </div>
                    @if($day->early_leave_minutes > 0)
                        <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                            <dt class="text-[13px] text-muted">Pulang lebih awal</dt>
                            <dd class="tabular-nums">{{ $day->early_leave_minutes }} menit</dd>
                        </div>
                    @endif
                    <div class="flex items-start justify-between gap-3 px-4 py-3 md:px-6">
                        <dt class="text-[13px] text-muted">Lokasi</dt>
                        <dd class="text-right">
                            {{ $day->location?->name ?? 'Tanpa lokasi' }}
                            @if($day->location)
                                <span class="block text-[12px] text-muted">{{ $day->location->address }}</span>
                            @endif
                        </dd>
                    </div>
                    @if($day->note)
                        <div class="px-4 py-3 md:px-6">
                            <dt class="text-[13px] text-muted">Catatan</dt>
                            <dd class="mt-1 text-[14px] leading-6">{{ $day->note }}</dd>
                        </div>
                    @endif
                    @if(count($day->flags ?? []))
                        <div class="px-4 py-3 md:px-6">
                            <dt class="text-[13px] text-muted">Tanda</dt>
                            <dd class="mt-1 flex flex-wrap gap-1.5">
                                @foreach($day->flags as $flag)
                                    <span class="rounded-[2px] border border-divider px-1.5 py-0.5 text-[11px] text-muted">{{ \App\Support\Flags::label($flag) }}</span>
                                @endforeach
                            </dd>
                        </div>
                    @endif
                </dl>
            </x-panel>

            <x-panel title="Jejak kejadian" description="Setiap percobaan absen disimpan, termasuk yang ditolak.">
                @if($events->isEmpty())
                    <div class="panel-pad">
                        <p class="text-[13px] text-muted">Tidak ada kejadian tercatat untuk hari ini.</p>
                    </div>
                @else
                    <ul class="divide-y divide-divider">
                        @foreach($events as $item)
                            @php($event = $item['event'])
                            <li class="flex flex-wrap items-start justify-between gap-3 px-4 py-4 md:px-6">
                                <div class="min-w-0">
                                    <p class="text-[14px] font-medium">
                                        {{ $event->type->label() }}
                                        <span class="ml-1 text-[12px] font-normal text-muted">{{ \App\Support\Waktu::time($event->occurred_at) }}</span>
                                    </p>
                                    <p class="mt-0.5 text-[12px] text-muted">
                                        {{ $event->status->label() }}
                                        @if($event->distance_meters !== null)
                                            · {{ $event->distance_meters }} m dari lokasi
                                        @endif
                                        @if($event->accuracy_meters !== null)
                                            · akurasi {{ (int) round((float) $event->accuracy_meters) }} m
                                        @endif
                                    </p>
                                    @if(count($event->flags ?? []))
                                        <p class="mt-1 text-[12px] text-muted">
                                            @foreach($event->flags as $flag)
                                                <span class="mr-2">{{ \App\Support\Flags::label($flag) }}</span>
                                            @endforeach
                                        </p>
                                    @endif
                                </div>

                                @if($item['selfie'])
                                    <a href="{{ $item['selfie'] }}" target="_blank" rel="noopener" class="shrink-0">
                                        <img src="{{ $item['selfie'] }}" alt="Selfie {{ $event->type->label() }}" class="h-16 w-16 rounded-[2px] border border-divider object-cover">
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-panel>

            @if($day->corrections->isNotEmpty())
                <x-panel title="Riwayat koreksi">
                    <ul class="divide-y divide-divider">
                        @foreach($day->corrections->sortByDesc('created_at') as $correction)
                            <li class="px-4 py-4 md:px-6">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <x-status :status="$correction->status" />
                                    <span class="text-[12px] text-muted">{{ \App\Support\Waktu::dateShort($correction->created_at) }}</span>
                                </div>
                                <p class="mt-2 text-[13px] leading-6">{{ $correction->reason }}</p>
                                <p class="mt-1 text-[12px] text-muted">Diajukan oleh {{ $correction->requestedBy?->name ?? 'anggota' }}</p>
                                @if($correction->review_note)
                                    <p class="mt-2 border-l-2 border-divider pl-3 text-[13px] leading-6 text-muted">{{ $correction->review_note }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </x-panel>
            @endif
        </div>

        <div class="space-y-4">
            @if($canRequestCorrection && ! $hasPendingCorrection)
                @if(! $showCorrectionForm)
                    <x-panel title="Ada yang keliru?">
                        <div class="panel-pad">
                            <p class="text-[13px] leading-6 text-muted">
                                Ajukan koreksi bila jam tidak sesuai. Koreksi dicatat sebagai pengajuan, bukan perubahan langsung.
                            </p>
                            <button type="button" wire:click="openCorrection" class="btn btn-secondary btn-sm mt-3 w-full">Ajukan koreksi</button>
                        </div>
                    </x-panel>
                @else
                    <x-panel title="Ajukan koreksi">
                        <form wire:submit="submitCorrection" class="panel-pad space-y-3">
                            <x-field name="correctionCheckIn" label="Jam masuk" type="time" wire:model="correctionCheckIn" help="Kosongkan bila tidak diubah." />
                            <x-field name="correctionCheckOut" label="Jam pulang" type="time" wire:model="correctionCheckOut" help="Kosongkan bila tidak diubah." />
                            <x-field name="correctionNote" label="Catatan hari" type="textarea" rows="2" wire:model="correctionNote" />
                            <x-field name="correctionReason" label="Alasan koreksi" type="textarea" rows="3" wire:model="correctionReason" required help="Jelaskan singkat penyebab kekeliruan." />

                            <div class="flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-1" wire:loading.attr="disabled" wire:target="submitCorrection">Kirim koreksi</button>
                                <button type="button" wire:click="$set('showCorrectionForm', false)" class="btn btn-ghost btn-sm">Batal</button>
                            </div>
                        </form>
                    </x-panel>
                @endif
            @elseif($hasPendingCorrection)
                <x-panel title="Koreksi menunggu">
                    <div class="panel-pad">
                        <p class="text-[13px] leading-6 text-muted">Pengajuan koreksi untuk hari ini sedang menunggu tinjauan.</p>
                    </div>
                </x-panel>
            @endif

            @if($canReview)
                <x-panel title="Tindakan peninjau">
                    <div class="panel-pad space-y-2">
                        <a href="{{ route('app.reviews.index') }}" class="btn btn-secondary btn-sm w-full">Buka antrean tinjauan</a>
                        <p class="text-[12px] leading-5 text-muted">
                            Koreksi dan tinjauan diputuskan dari antrean agar setiap keputusan punya catatan.
                        </p>
                    </div>
                </x-panel>
            @endif
        </div>
    </div>
</div>
