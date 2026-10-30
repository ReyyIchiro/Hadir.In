<div>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="page-title">Izin dan cuti</h1>
            <p class="mt-1 text-[14px] text-muted">{{ $isTeam ? 'Pengajuan anggota pada lingkup Anda.' : 'Pengajuan izin dan cuti Anda.' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($canReview)
                <button type="button" wire:click="$set('scope', '{{ $scope === 'team' ? 'mine' : 'team' }}')" class="btn btn-secondary btn-sm">
                    {{ $scope === 'team' ? 'Lihat pengajuan saya' : 'Lihat tim' }}
                </button>
            @endif
            <a href="{{ route('app.leave.create') }}" class="btn btn-primary btn-sm">Ajukan izin</a>
        </div>
    </div>

    <div class="mt-5 flex flex-wrap items-center gap-2 border-y border-divider py-3">
        <select wire:model.live="statusFilter" class="field-input w-auto py-1.5 text-[13px]" aria-label="Filter status">
            <option value="">Semua status</option>
            @foreach($statuses as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <span class="text-[12px] text-muted">{{ $leaves->total() }} pengajuan</span>
    </div>

    @if($leaves->isEmpty())
        <x-empty-state title="Belum ada pengajuan" description="Pengajuan izin, sakit, atau cuti akan muncul di sini.">
            <x-slot:action>
                <a href="{{ route('app.leave.create') }}" class="btn btn-secondary btn-sm">Ajukan sekarang</a>
            </x-slot:action>
        </x-empty-state>
    @else
        <x-panel class="mt-4">
            <div class="table-scroll">
                <table class="ledger">
                    <caption>Daftar pengajuan izin dan cuti</caption>
                    <thead>
                        <tr>
                            @if($isTeam)<th scope="col">Anggota</th>@endif
                            <th scope="col">Jenis</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Hari</th>
                            <th scope="col">Diajukan</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Tindakan</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaves as $leave)
                            <tr>
                                @if($isTeam)<td>{{ $leave->user?->name ?? '--' }}</td>@endif
                                <td>{{ $leave->type->label() }}</td>
                                <td class="tabular-nums">
                                    {{ \App\Support\Waktu::dateShort($leave->start_date) }}
                                    @if(! $leave->start_date->equalTo($leave->end_date))
                                        <span class="block text-[12px] text-muted">sampai {{ \App\Support\Waktu::dateShort($leave->end_date) }}</span>
                                    @endif
                                </td>
                                <td class="tabular-nums">{{ $leave->durationInDays() }}</td>
                                <td class="text-[12px] text-muted">{{ \App\Support\Waktu::dateShort($leave->created_at) }}</td>
                                <td><x-status :status="$leave->status" /></td>
                                <td class="text-right">
                                    <a href="{{ route('app.leave.show', $leave) }}" class="link text-[12px]">Lihat</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-panel>

        <div class="mt-4">{{ $leaves->links() }}</div>
    @endif
</div>
