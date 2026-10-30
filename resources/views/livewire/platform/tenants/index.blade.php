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
                            <th scope="col">Nama</th>
                            <th scope="col">Status</th>
                            <th scope="col">Anggota</th>
                            <th scope="col">Zona waktu</th>
                            <th scope="col">Dibuat</th>
                            <th scope="col"><span class="sr-only">Tindakan</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tenants as $tenant)
                            <tr>
                                <td>
                                    <a href="{{ route('platform.tenants.show', $tenant) }}" class="link">{{ $tenant->name }}</a>
                                    <p class="text-[11px] text-muted">{{ $tenant->slug }}</p>
                                </td>
                                <td><x-status :status="$tenant->status->value" :label="$tenant->status->label()" /></td>
                                <td class="tabular-nums">{{ $tenant->memberships_count }}</td>
                                <td class="text-muted">{{ $tenant->timezone }}</td>
                                <td class="tabular-nums text-muted">{{ \App\Support\Waktu::dateShort($tenant->created_at) }}</td>
                                <td class="text-right">
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
