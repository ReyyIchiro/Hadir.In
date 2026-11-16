<div class="max-w-xl">
    <a href="{{ route('app.leave.index') }}" class="link inline-flex items-center gap-1 text-[13px]">
        <x-icon name="chevron-left" :size="14" />
        <span>Kembali ke izin dan cuti</span>
    </a>

    <h1 class="page-title mt-2">Ajukan izin</h1>
    <p class="mt-1 text-[14px] leading-6 text-muted">
        Pengajuan akan ditinjau oleh peninjau. Persetujuan muncul di kalender presensi Anda.
    </p>

    <form wire:submit="submit" class="mt-5 space-y-4">
        <x-panel title="Detail pengajuan">
            <div class="panel-pad space-y-4">
                <x-select name="type" label="Jenis" wire:model.live="type" :options="$types" required help="Cuti hanya dapat diajukan pada hari kerja terjadwal." />

                <div class="grid gap-3 sm:grid-cols-2">
                    <x-field name="startDate" label="Tanggal mulai" type="date" wire:model="startDate" required :help="$startDate ? \App\Support\Waktu::dateLong($startDate) : null" />
                    <x-field name="endDate" label="Tanggal selesai" type="date" wire:model="endDate" required :help="$endDate ? \App\Support\Waktu::dateLong($endDate) : 'Maksimal 30 hari.'" />
                </div>

                <x-field name="reason" label="Alasan" type="textarea" rows="4" wire:model="reason" required help="Tuliskan alasan singkat dan jelas." />
            </div>
        </x-panel>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">Kirim pengajuan</span>
                <span wire:loading wire:target="submit">Mengirim...</span>
            </button>
            <a href="{{ route('app.leave.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
