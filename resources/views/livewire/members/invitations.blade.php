<div>
    <a href="{{ route('app.members.index') }}" class="link inline-flex items-center gap-1 text-[13px]">
        <x-icon name="chevron-left" :size="14" />
        <span>Kembali ke anggota</span>
    </a>

    <div class="mt-2 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="page-title">Undangan</h1>
            <p class="mt-1 max-w-2xl text-[14px] leading-6 text-muted">Undangan berlaku 7 hari dan hanya dapat dipakai sekali.</p>
        </div>
        <a href="{{ route('app.members.invite') }}" class="btn btn-primary btn-sm">Undang anggota</a>
    </div>

    @if($flash)
        <div class="mt-4 rounded-[4px] border border-divider bg-surface px-4 py-3 text-[13px] text-muted" role="status">{{ $flash }}</div>
    @endif

    <x-panel class="mt-5" title="Riwayat undangan">
        @if($invitations->isEmpty())
            <x-empty-state title="Belum ada undangan" description="Undangan yang Anda buat akan muncul di sini beserta statusnya." />
        @else
            <div class="table-scroll">
                <table class="ledger">
                    <caption>Undangan organisasi</caption>
                    <thead>
                        <tr>
                            <th scope="col">Email</th>
                            <th scope="col">Peran</th>
                            <th scope="col">Lokasi</th>
                            <th scope="col">Berlaku sampai</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Tindakan</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invitations as $invitation)
                            <tr>
                                <td>{{ $invitation->email }}</td>
                                <td>{{ $invitation->role->label() }}</td>
                                <td class="text-muted">{{ $invitation->primaryLocation?->name ?? '--' }}</td>
                                <td class="tabular-nums text-muted">{{ \App\Support\Waktu::dateShort($invitation->expires_at) }}</td>
                                <td>
                                    @if($invitation->accepted_at)
                                        <span class="status-pill status-approved">Diterima</span>
                                    @elseif($invitation->revoked_at)
                                        <span class="status-pill status-cancelled">Dicabut</span>
                                    @elseif($invitation->expires_at->isPast())
                                        <span class="status-pill status-absen">Kedaluwarsa</span>
                                    @else
                                        <span class="status-pill status-pending">Menunggu</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($invitation->isPending())
                                        <button type="button" wire:click="revoke({{ $invitation->id }})" wire:confirm="Cabut undangan ini?" class="link text-[12px]">Cabut</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-panel>
</div>
