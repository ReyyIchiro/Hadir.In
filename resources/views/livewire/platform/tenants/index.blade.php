<div>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="eyebrow">Platform</p>
            <h1 class="page-title mt-1">Organisasi</h1>
        </div>
    </div>

    <x-panel class="mt-5">
        <div class="grid gap-3 border-b border-divider px-4 py-3 sm:grid-cols-2 md:px-6">
            <x-field name="search" label="Cari" wire:model.live.debounce.300ms="search" placeholder="Nama atau slug" />
            <x-select name="statusFilter" label="Status" wire:model.live="statusFilter" placeholder="Semua status" :options="$statuses" />
        </div>

        @if($tenants->isEmpty())
            <x-empty-state title="Tidak ada organisasi" description="Ubah filter atau tunggu organisasi baru mendaftar." />
        @else
            <div class="table-scroll">
                <table class="ledger">
                    <caption>Daftar organisasi</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="cell-grow">Nama</th>
                            <th scope="col" class="cell-status">Status</th>
                            <th scope="col" class="num-head cell-fit">Anggota</th>
                            <th scope="col" class="cell-fit">Zona waktu</th>
                            <th scope="col" class="cell-fit">Dibuat</th>
                            <th scope="col" class="cell-action"><span class="sr-only">Tindakan</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tenants as $tenant)
                            <tr>
                                <td class="cell-grow">
                                    <a href="{{ route('platform.tenants.show', $tenant) }}" class="link">{{ $tenant->name }}</a>
                                    <p class="text-[11px] text-muted">{{ $tenant->slug }}</p>
                                </td>
                                <td class="cell-status"><x-status :status="$tenant->status->value" :label="$tenant->status->label()" /></td>
                                <td class="cell-num cell-fit">{{ $tenant->memberships_count }}</td>
                                <td class="cell-fit text-muted">{{ $tenant->timezone }}</td>
                                <td class="cell-date cell-fit cell-meta">{{ \App\Support\Waktu::dateShort($tenant->created_at) }}</td>
                                <td class="cell-action">
                                    <a href="{{ route('platform.tenants.show', $tenant) }}" class="link text-[12px]">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-divider px-4 py-3 md:px-6">{{ $tenants->links() }}</div>
        @endif
    </x-panel>
</div>
