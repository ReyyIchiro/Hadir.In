<div>
    <a href="{{ route('app.members.index') }}" class="link inline-flex items-center gap-1 text-[13px]">
        <x-icon name="chevron-left" :size="14" />
        <span>Kembali ke anggota</span>
    </a>

    <h1 class="page-title mt-2">{{ $member->user?->name }}</h1>
    <p class="mt-1 text-[14px] text-muted">{{ $member->user?->email }}</p>

    @if($flash)
        <div class="mt-4 rounded-[4px] border border-divider bg-surface px-4 py-3 text-[13px] text-muted" role="status">{{ $flash }}</div>
    @endif

    <form wire:submit="save" class="mt-5 grid gap-4 lg:grid-cols-[1fr_320px]">
        <x-panel title="Keanggotaan">
            <div class="panel-pad grid gap-4 sm:grid-cols-2">
                <x-select name="role" label="Peran" wire:model.live="role" required :options="$roles" />
                <x-select name="primary_location_id" label="Lokasi utama" wire:model="primary_location_id" placeholder="Tanpa lokasi" :options="$locations->pluck('name', 'id')->all()" help="Menentukan cakupan peninjauan." />
                <x-select name="division_id" label="Divisi" wire:model="division_id" placeholder="Tanpa divisi" :options="$divisions->pluck('name', 'id')->all()" />
                <div class="flex items-end pb-2">
                    <label class="flex items-center gap-2 text-[13px]">
                        <input type="checkbox" wire:model="is_active" class="h-4 w-4 rounded-[2px] border-divider text-stamp focus:ring-focus">
                        <span>Keanggotaan aktif</span>
                    </label>
                </div>
            </div>
        </x-panel>

        <div class="space-y-4">
            <x-panel title="Riwayat">
                <dl class="divide-y divide-divider text-[13px]">
                    <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                        <dt class="text-muted">Bergabung</dt>
                        <dd>{{ $member->joined_at ? \App\Support\Waktu::dateShort($member->joined_at) : '--' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                        <dt class="text-muted">Peran saat ini</dt>
                        <dd>{{ $member->role->label() }}</dd>
                    </div>
                </dl>
            </x-panel>

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Simpan perubahan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
                <a href="{{ route('app.members.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </div>
    </form>
</div>
