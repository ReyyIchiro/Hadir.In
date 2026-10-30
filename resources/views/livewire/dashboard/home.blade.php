<div>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="folio">{{ \App\Support\Waktu::dayName($today) }} / {{ \App\Support\Waktu::dateShort($today) }}</span>
                <span class="h-px w-6 bg-rule-strong" aria-hidden="true"></span>
            </div>
            <h1 class="page-title mt-2">{{ auth()->user()->name }}</h1>
            <p class="mt-1 text-[13px] text-muted">
                {{ $schedule && ! $schedule->is_day_off ? 'Jadwal '.substr((string) $schedule->start_time, 0, 5).' - '.substr((string) $schedule->end_time, 0, 5) : 'Tidak ada jadwal hari ini' }}
            </p>
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

    <div class="mt-6 grid gap-4 lg:grid-cols-[1.35fr_1fr]">
        {{-- Kartu presensi hari ini: Tiga Bukti sebagai inti. --}}
        <x-panel>
            <div class="panel-pad">
                @if($leave)
                    <div class="flex flex-wrap items-center gap-3">
                        <x-status :status="$leave->type->value" />
                        <p class="text-[14px] leading-6">
                            Ada pengajuan {{ $leave->type->label() }} berstatus {{ $leave->status->label() }} pada tanggal ini.
                        </p>
                    </div>
                @elseif($day)
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="label">Presensi hari ini</p>
                            <p class="mt-2 text-[14px] text-muted">{{ \App\Support\Waktu::dateLong($today) }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <x-status :status="$day->displayStatus()" />
                            @if(in_array('perlu_ditinjau', $day->flags ?? [], true))
                                <span class="stamp">Perlu ditinjau</span>
                            @endif
                        </div>
                    </div>

                    <dl class="proof-strip mt-5">
                        <div class="proof-cell">
                            <dt class="proof-label">Masuk</dt>
                            <dd class="proof-time mt-1">{{ \App\Support\Waktu::time($day->check_in_at) }}</dd>
                        </div>
                        <div class="proof-cell">
                            <dt class="proof-label">Pulang</dt>
                            <dd class="proof-time mt-1">{{ \App\Support\Waktu::time($day->check_out_at) }}</dd>
                        </div>
                        <div class="proof-cell">
                            <dt class="proof-label">Terlambat</dt>
                            <dd class="proof-time mt-1">{{ $day->late_minutes }}<span class="ml-1 font-sans text-[13px] text-muted">mnt</span></dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap gap-x-4 gap-y-1">
                            @forelse($day->flags ?? [] as $flag)
                                <span class="text-[12px] text-muted">{{ \App\Support\Flags::label($flag) }}</span>
                            @empty
                                <span class="text-[12px] text-muted">Tanpa catatan khusus.</span>
                            @endforelse
                        </div>
                        <a href="{{ route('app.attendance.show', $day) }}" class="arrow-link">Lihat detail</a>
                    </div>
                @else
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="label">Presensi hari ini</p>
                            <p class="mt-2 text-[14px] text-muted">{{ \App\Support\Waktu::dateLong($today) }}</p>
                        </div>
                        <x-status status="belum_absen" />
                    </div>

                    <dl class="proof-strip mt-5">
                        <div class="proof-cell">
                            <dt class="proof-label">Masuk</dt>
                            <dd class="proof-time mt-1 text-muted">--.--</dd>
                        </div>
                        <div class="proof-cell">
                            <dt class="proof-label">Pulang</dt>
                            <dd class="proof-time mt-1 text-muted">--.--</dd>
                        </div>
                        <div class="proof-cell">
                            <dt class="proof-label">Jendela absen</dt>
                            <dd class="mt-2 text-[13px] leading-5 text-muted">Dibuka 60 menit sebelum jam mulai.</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <p class="max-w-[44ch] text-[12px] leading-5 text-muted">
                            Catatan muncul setelah Anda absen masuk. Waktu diambil dari server organisasi.
                        </p>
                        @if(! $leave)
                            <a href="{{ route('app.attendance.check-in') }}" class="arrow-link">Absen sekarang</a>
                        @endif
                    </div>
                @endif
            </div>
        </x-panel>

        {{-- Ringkasan bulan ini: satu panel, garis, bukan empat kotak. --}}
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
            <div class="flex items-center justify-between gap-3 border-t border-divider px-4 py-3">
                <p class="text-[12px] text-muted">Rekap dihitung dari catatan yang sudah final.</p>
                <a href="{{ route('app.attendance.index') }}" class="arrow-link">Riwayat</a>
            </div>
        </x-panel>
    </div>

    @if($canReview)
        <div class="mt-4 grid gap-4 lg:grid-cols-[1fr_1fr]">
            <x-panel title="Perlu tindakan" description="Antrean yang menunggu keputusan Anda.">
                <div class="divide-y divide-divider">
                    <a href="{{ route('app.reviews.index') }}" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-tint md:px-6">
                        <span class="text-[14px]">Tinjauan presensi</span>
                        <span class="data-lg text-[18px]">{{ $pendingReviews }}</span>
                    </a>
                    <a href="{{ route('app.reviews.index', ['tab' => 'koreksi']) }}" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-tint md:px-6">
                        <span class="text-[14px]">Koreksi presensi</span>
                        <span class="data-lg text-[18px]">{{ $pendingCorrections }}</span>
                    </a>
                    <a href="{{ route('app.reviews.index', ['tab' => 'izin']) }}" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-tint md:px-6">
                        <span class="text-[14px]">Izin dan cuti</span>
                        <span class="data-lg text-[18px]">{{ $pendingLeave }}</span>
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
                    <div class="border-t border-divider px-4 py-3">
                        <a href="{{ route('app.attendance.index', ['view' => 'team']) }}" class="arrow-link">Buka presensi tim</a>
                    </div>
                </x-panel>
            @endif
        </div>
    @endif
</div>
