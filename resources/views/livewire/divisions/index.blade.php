<div>
    <h1 class="page-title">Divisi</h1>
    <p class="mt-1 max-w-2xl text-[14px] leading-6 text-muted">
        Divisi membantu menyaring anggota pada jadwal dan laporan. Satu anggota hanya berada di satu divisi.
    </p>

    @if($flash)
        <div class="mt-4 rounded-[4px] border border-divider bg-surface px-4 py-3 text-[13px] text-muted" role="status">{{ $flash }}</div>
    @endif

    <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_320px]">
        <x-panel title="Daftar divisi">
            @if($divisions->isEmpty())
                <x-empty-state title="Belum ada divisi" description="Tambahkan divisi untuk mengelompokkan anggota, misalnya Toko atau Operasional." />
            @else
                <div class="table-scroll">
                    <table class="ledger">
                        <caption>Divisi organisasi</caption>
                        <thead>
                            <tr>
                                <th scope="col">Nama</th>
                                <th scope="col">Anggota</th>
                                <th scope="col">Status</th>
                                <th scope="col"><span class="sr-only">Tindakan</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($divisions as $division)
                                <tr>
                                    <td>
                                        <span class="font-medium">{{ $division->name }}</span>
                                        @if($division->description)
                                            <p class="mt-0.5 text-[12px] text-muted">{{ $division->description }}</p>
                                        @endif
                                    </td>
                                    <td class="tabular-nums">{{ $division->memberships_count }}</td>
                                    <td>
                                        <span class="status-pill {{ $division->is_active ? 'status-active' : 'status-cancelled' }}">
                                            {{ $division->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="space-x-3 text-right">
                                        <button type="button" wire:click="edit({{ $division->id }})" class="link text-[12px]">Ubah</button>
                                        <button type="button" wire:click="toggle({{ $division->id }})" class="link text-[12px]">
                                            {{ $division->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-panel>

        <x-panel :title="$editingId ? 'Ubah divisi' : 'Tambah divisi'">
            <form wire:submit="save" class="panel-pad space-y-3">
                <x-field name="name" label="Nama divisi" wire:model="name" required placeholder="Contoh: Toko Bandung" />
                <x-field name="description" label="Keterangan" type="textarea" rows="3" wire:model="description" />
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-1">{{ $editingId ? 'Simpan' : 'Tambah' }}</button>
                    @if($editingId)
                        <button type="button" wire:click="cancelEdit" class="btn btn-ghost btn-sm">Batal</button>
                    @endif
                </div>
            </form>
        </x-panel>
    </div>
</div>
