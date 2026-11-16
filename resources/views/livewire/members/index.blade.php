<div>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="page-title">Anggota</h1>
            <p class="mt-1 max-w-2xl text-[14px] leading-6 text-muted">
                Kelola peran, lokasi utama, dan divisi. Perubahan berlaku pada peninjauan dan laporan berikutnya.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('app.invitations.index') }}" class="btn btn-secondary btn-sm">
                Undangan tertunda
                @if($pendingInvitations > 0)
                    <span class="ml-1 tabular-nums text-muted">({{ $pendingInvitations }})</span>
                @endif
            </a>
            <a href="{{ route('app.members.invite') }}" class="btn btn-primary btn-sm">Undang anggota</a>
        </div>
    </div>

    <x-panel class="mt-5">
        <div class="grid gap-3 border-b border-divider px-4 py-3 sm:grid-cols-2 lg:grid-cols-4 md:px-6">
            <x-field name="search" label="Cari" wire:model.live.debounce.300ms="search" placeholder="Nama atau email" />
            <x-select name="roleFilter" label="Peran" wire:model.live.debounce.150ms="roleFilter" placeholder="Semua peran" :options="$roles" />
            <x-select name="locationFilter" label="Lokasi utama" wire:model.live.debounce.150ms="locationFilter" placeholder="Semua lokasi" :options="$locations->pluck('name', 'id')->all()" />
            <x-select name="divisionFilter" label="Divisi" wire:model.live.debounce.150ms="divisionFilter" placeholder="Semua divisi" :options="$divisions->pluck('name', 'id')->all()" />
            <p class="col-span-full text-[12px] text-muted" style="display: none" wire:loading wire:target="search, roleFilter, locationFilter, divisionFilter">Memperbarui...</p>
        </div>

        @if($members->isEmpty())
            <x-empty-state title="Tidak ada anggota" description="Ubah filter atau undang anggota baru ke organisasi ini." />
        @else
            <div class="table-scroll">
                <table class="ledger">
                    <caption>Anggota aktif</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="cell-grow">Nama</th>
                            <th scope="col" class="cell-fit">Peran</th>
                            <th scope="col">Lokasi utama</th>
                            <th scope="col">Divisi</th>
                            <th scope="col" class="cell-fit">Bergabung</th>
                            <th scope="col" class="cell-action"><span class="sr-only">Tindakan</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $member)
                            <tr wire:key="member-{{ $member->id }}">
                                <td class="cell-grow">
                                    <span class="font-medium">{{ $member->user?->name }}</span>
                                    <p class="mt-0.5 text-[12px] text-muted">{{ $member->user?->email }}</p>
                                </td>
                                <td class="cell-fit">{{ $member->role->label() }}</td>
                                <td class="text-muted">{{ $member->primaryLocation?->name ?? 'Belum diatur' }}</td>
                                <td class="text-muted">{{ $member->division?->name ?? 'Tanpa divisi' }}</td>
                                <td class="cell-date cell-fit cell-meta">{{ $member->joined_at ? \App\Support\Waktu::dateShort($member->joined_at) : '--' }}</td>
                                <td class="cell-action">
                                    @if($member->role !== \App\Enums\MembershipRole::Owner)
                                        <a href="{{ route('app.members.edit', $member) }}" class="link text-[12px]">Ubah</a>
                                    @else
                                        <span class="text-[12px] text-muted">Pemilik</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-divider px-4 py-3 md:px-6">{{ $members->links() }}</div>
        @endif
    </x-panel>
</div>
