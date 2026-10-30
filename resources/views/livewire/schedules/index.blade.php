<div>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="page-title">Jadwal</h1>
            <p class="mt-1 text-[14px] text-muted">
                {{ $canManage ? 'Atur jadwal kerja anggota per hari.' : 'Jadwal kerja Anda minggu ini.' }}
            </p>
        </div>
        @if($canManage)
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('app.schedules.bulk') }}" class="btn btn-primary btn-sm">
                    <x-icon name="calendar" :size="16" />
                    <span>Terapkan massal</span>
                </a>
            </div>
        @endif
    </div>

    <div class="mt-5 flex flex-wrap items-end gap-3 border-y border-divider py-3">
        <div class="flex items-center gap-1">
            <button type="button" wire:click="previousWeek" class="btn btn-ghost btn-sm px-2" aria-label="Minggu sebelumnya">
                <x-icon name="chevron-left" :size="16" />
            </button>
            <button type="button" wire:click="thisWeek" class="btn btn-ghost btn-sm">Minggu ini</button>
            <button type="button" wire:click="nextWeek" class="btn btn-ghost btn-sm px-2" aria-label="Minggu berikutnya">
                <x-icon name="chevron-right" :size="16" />
            </button>
        </div>

        <p class="font-mono text-[13px] font-medium">
            {{ \App\Support\Waktu::dateShort($weekStart) }} - {{ \App\Support\Waktu::dateShort($dates->last()) }}
        </p>

        @if($canManage)
            <div class="ml-auto flex flex-wrap gap-2">
                <select wire:model.live="divisionFilter" class="field-input w-auto py-1.5 text-[13px]" aria-label="Filter divisi">
                    <option value="">Semua divisi</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="memberFilter" class="field-input w-auto py-1.5 text-[13px]" aria-label="Filter anggota">
                    <option value="">Semua anggota</option>
                    @foreach($allMembers as $member)
                        <option value="{{ $member->user_id }}">{{ $member->user->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    @if($memberships->isEmpty())
        <x-empty-state title="Belum ada jadwal untuk ditampilkan" description="Tambahkan anggota lebih dulu, lalu terapkan jadwal kerja." />
    @else
        <div class="table-scroll mt-4">
            <table class="ledger">
                <caption>Jadwal mingguan</caption>
                <thead>
                    <tr>
                        <th scope="col" class="sticky left-0 bg-surface">Anggota</th>
                        @foreach($dates as $date)
                            <th scope="col" class="min-w-[108px] text-center">
                                {{ \App\Support\Waktu::dayName($date) }}
                                <span class="block text-[11px] font-normal text-muted">{{ \App\Support\Waktu::dateShort($date) }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($memberships as $member)
                        <tr>
                            <th scope="row" class="sticky left-0 bg-surface text-left font-medium">
                                {{ $member->user->name }}
                                <span class="block text-[11px] font-normal text-muted">{{ $member->role->label() }}</span>
                            </th>
                            @foreach($dates as $date)
                                @php($schedule = $schedules->get($member->user_id.'|'.$date->toDateString()))
                                <td class="text-center align-middle">
                                    @if($schedule)
                                        @if($schedule->is_day_off)
                                            <span class="text-[12px] text-muted">Libur</span>
                                        @else
                                            <span class="block text-[12px] tabular-nums">{{ substr((string) $schedule->start_time, 0, 5) }}</span>
                                            <span class="block text-[12px] tabular-nums text-muted">{{ substr((string) $schedule->end_time, 0, 5) }}</span>
                                        @endif
                                        @if($canManage)
                                            <a href="{{ route('app.schedules.edit', $schedule) }}" class="link mt-1 inline-block text-[11px]">Ubah</a>
                                        @endif
                                    @else
                                        <span class="text-[12px] text-muted">--</span>
                                        @if($canManage)
                                            <a href="{{ route('app.schedules.bulk', ['member' => $member->user_id, 'date' => $date->toDateString()]) }}" class="link mt-1 inline-block text-[11px]">Atur</a>
                                        @endif
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
