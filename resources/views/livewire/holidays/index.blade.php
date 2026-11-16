<div>
    <h1 class="page-title">Hari libur</h1>
    <p class="mt-1 max-w-2xl text-[14px] leading-6 text-muted">
        Hari libur tidak dihitung sebagai hari kerja. Anggota yang tetap masuk pada hari libur tidak dianggap absen.
    </p>

    @if($flash)
        <div class="mt-4 rounded-[4px] border border-divider bg-surface px-4 py-3 text-[13px] text-muted" role="status">{{ $flash }}</div>
    @endif

    <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_320px]">
        <x-panel title="Daftar hari libur">
            @if($holidays->isEmpty())
                <x-empty-state title="Belum ada hari libur" description="Tambahkan tanggal libur nasional atau libur organisasi." />
            @else
                <div class="table-scroll">
                    <table class="ledger">
                        <caption>Hari libur organisasi</caption>
                        <thead>
                            <tr>
                                <th scope="col">Tanggal</th>
                                <th scope="col" class="cell-fit">Hari</th>
                                <th scope="col" class="cell-grow">Nama</th>
                                <th scope="col" class="cell-action"><span class="sr-only">Tindakan</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($holidays as $holiday)
                                <tr>
                                    <td class="cell-date cell-fit">{{ \App\Support\Waktu::dateShort($holiday->date) }}</td>
                                    <td class="cell-fit text-muted">{{ \App\Support\Waktu::dayName($holiday->date) }}</td>
                                    <td class="cell-grow">{{ $holiday->name }}</td>
                                    <td class="cell-action">
                                        <button type="button" wire:click="delete({{ $holiday->id }})" wire:loading.attr="disabled" wire:target="delete" data-confirm="Hapus hari libur ini?" data-confirm-detail="Tanggal ini kembali dihitung sebagai hari kerja. Presensi yang sudah tercatat tidak berubah." data-confirm-action="Hapus" class="link text-[12px]">Hapus</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-panel>

        <x-panel title="Tambah hari libur">
            <form wire:submit="save" class="panel-pad space-y-3">
                <x-field name="date" label="Tanggal" type="date" wire:model="date" required :help="$date ? \App\Support\Waktu::dateLong($date) : null" />
                <x-field name="name" label="Nama" wire:model="name" required placeholder="Contoh: Hari Kemerdekaan" />
                <button type="submit" class="btn btn-primary btn-sm w-full">Tambah</button>
            </form>
        </x-panel>
    </div>
</div>
