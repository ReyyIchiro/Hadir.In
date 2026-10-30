<div>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="eyebrow">{{ \App\Support\Waktu::dayName($today) }}, {{ \App\Support\Waktu::dateLong($today) }}</p>
            <h1 class="page-title mt-1">{{ auth()->user()->name }}</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($day?->check_in_at && ! $day->check_out_at)
                <a href="{{ route('app.attendance.check-out') }}" class="btn btn-primary">Absen pulang</a>
            @elseif(! $day?->check_in_at && ! $leave)
                <a href="{{ route('app.attendance.check-in') }}" class="btn btn-primary">Absen masuk</a>
            @endif
            <a href="{{ route('app.leave.create') }}" class="btn btn-secondary">Ajukan izin</a>
        </div>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-[1.2fr_1fr]">
        <x-panel title="Presensi hari ini" :description="$schedule ? 'Jadwal '.substr((string) $schedule->start_time, 0, 5).' - '.substr((string) $schedule->end_time, 0, 5) : 'Tidak ada jadwal hari ini'">
            <div class="panel-pad">
                @if($leave)
                    <div class="flex flex-wrap items-center gap-3">
                        <x-status :status="$leave->type->value" />
                        <p class="text-[14px] leading-6">
                            Ada pengajuan {{ $leave->type->label() }} berstatus {{ $leave->status->label() }} pada tanggal ini.
                        </p>
                    </div>
                @elseif($day)
                    <div class="flex flex-wrap items-center gap-3">
                        <x-status :status="$day->displayStatus()" />
                        @foreach($day->flags ?? [] as $flag)
                            <span class="text-[12px] text-muted">{{ \App\Support\Flags::label($flag) }}</span>
                        @endforeach
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3 text-[14px] sm:grid-cols-3">
                        <div>
                            <dt class="text-[12px] text-muted">Masuk</dt>
                            <dd class="mt-0.5 font-display text-[20px] font-semibold tabular-nums">{{ \App\Support\Waktu::time($day->check_in_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[12px] text-muted">Pulang</dt>
                            <dd class="mt-0.5 font-display text-[20px] font-semibold tabular-nums">{{ \App\Support\Waktu::time($day->check_out_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[12px] text-muted">Terlambat</dt>
                            <dd class="mt-0.5 font-display text-[20px] font-semibold tabular-nums">{{ $day->late_minutes }} menit</dd>
                        </div>
                    </dl>

                    <a href="{{ route('app.attendance.show', $day) }}" class="link mt-4 inline-block text-[13px]">Lihat detail hari ini</a>
                @else
                    <x-empty-state
                        title="Belum ada catatan hari ini"
                        description="Catatan muncul setelah Anda absen masuk. Jendela absen dibuka 60 menit sebelum jam mulai."
                    />
                @endif
            </div>
        </x-panel>

        <x-panel title="Bulan ini" description="{{ \App\Support\Waktu::monthLabel($today->month) }} {{ $today->year }}">
            <div class="ledger-stats">
                <div>
                    <p class="text-[12px] text-muted">Hadir</p>
                    <p class="stat-value mt-1">{{ $stats['hadir'] }}</p>
                </div>
                <div>
                    <p class="text-[12px] text-muted">Terlambat</p>
                    <p class="stat-value mt-1">{{ $stats['terlambat'] }}</p>
                </div>
                <div>
                    <p class="text-[12px] text-muted">Izin dan cuti</p>
                    <p class="stat-value mt-1">{{ $stats['izin'] }}</p>
                </div>
                <div>
                    <p class="text-[12px] text-muted">Absen</p>
                    <p class="stat-value mt-1">{{ $stats['absen'] }}</p>
                </div>
            </div>
        </x-panel>
    </div>

    @if($canReview)
        <div class="mt-6 grid gap-4 lg:grid-cols-[1fr_1fr]">
            <x-panel title="Perlu tindakan" description="Antrean yang menunggu keputusan Anda.">
                <div class="divide-y divide-divider">
                    <a href="{{ route('app.reviews.index') }}" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-tint md:px-6">
                        <span class="text-[14px]">Tinjauan presensi</span>
                        <span class="font-display text-[18px] font-semibold tabular-nums">{{ $pendingReviews }}</span>
                    </a>
                    <a href="{{ route('app.reviews.index', ['tab' => 'koreksi']) }}" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-tint md:px-6">
                        <span class="text-[14px]">Koreksi presensi</span>
                        <span class="font-display text-[18px] font-semibold tabular-nums">{{ $pendingCorrections }}</span>
                    </a>
                    <a href="{{ route('app.reviews.index', ['tab' => 'izin']) }}" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-tint md:px-6">
                        <span class="text-[14px]">Izin dan cuti</span>
                        <span class="font-display text-[18px] font-semibold tabular-nums">{{ $pendingLeave }}</span>
                    </a>
                </div>
            </x-panel>

            @if($teamToday)
                <x-panel title="Tim hari ini" description="Angka dihitung dari jadwal dan catatan presensi.">
                    <div class="ledger-stats">
                        <div>
                            <p class="text-[12px] text-muted">Dijadwalkan</p>
                            <p class="stat-value mt-1">{{ $teamToday['scheduled'] }}</p>
                        </div>
                        <div>
                            <p class="text-[12px] text-muted">Sudah masuk</p>
                            <p class="stat-value mt-1">{{ $teamToday['checked_in'] }}</p>
                        </div>
                        <div>
                            <p class="text-[12px] text-muted">Belum masuk</p>
                            <p class="stat-value mt-1">{{ $teamToday['absent'] }}</p>
                        </div>
                        <div>
                            <p class="text-[12px] text-muted">Perlu ditinjau</p>
                            <p class="stat-value mt-1">{{ $pendingReviews + $pendingCorrections }}</p>
                        </div>
                    </div>
                </x-panel>
            @endif
        </div>
    @endif
</div>
