<div>
    <a href="{{ route('app.schedules.index') }}" class="link inline-flex items-center gap-1 text-[13px]">
        <x-icon name="chevron-left" :size="14" />
        <span>Kembali ke jadwal</span>
    </a>

    <h1 class="page-title mt-2">{{ $member?->name ?? 'Jadwal' }}</h1>
    <p class="mt-1 text-[14px] text-muted">{{ \App\Support\Waktu::dayName($schedule->work_date) }}, {{ \App\Support\Waktu::dateLong($schedule->work_date) }}</p>

    @if($flash)
        <div class="mt-4 rounded-[4px] border border-present/40 bg-present/5 px-4 py-3 text-[13px] text-present" role="status">{{ $flash }}</div>
    @endif

    <form wire:submit="save" class="mt-5 max-w-xl space-y-4">
        <x-panel title="Pengaturan hari">
            <div class="panel-pad space-y-4">
                <label class="flex items-center gap-2 text-[14px]">
                    <input type="checkbox" wire:model.live="isDayOff" class="h-4 w-4 rounded-[2px] border-divider text-stamp focus:ring-stamp">
                    <span>Hari libur untuk anggota ini</span>
                </label>

                @if(! $isDayOff)
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-field name="startTime" label="Jam mulai" type="time" wire:model="startTime" required />
                        <x-field name="endTime" label="Jam selesai" type="time" wire:model="endTime" required />
                        <x-field name="breakStart" label="Istirahat mulai" type="time" wire:model="breakStart" />
                        <x-field name="breakEnd" label="Istirahat selesai" type="time" wire:model="breakEnd" />
                    </div>
                @endif

                <x-field name="note" label="Catatan" wire:model="note" help="Opsional." />
            </div>
        </x-panel>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">Simpan jadwal</button>
            <button type="button" wire:click="delete" wire:confirm="Hapus jadwal pada tanggal ini?" class="btn btn-ghost">Hapus jadwal</button>
        </div>
    </form>
</div>
