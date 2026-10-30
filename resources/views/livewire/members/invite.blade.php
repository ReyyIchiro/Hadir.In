<div>
    <a href="{{ route('app.members.index') }}" class="link inline-flex items-center gap-1 text-[13px]">
        <x-icon name="chevron-left" :size="14" />
        <span>Kembali ke anggota</span>
    </a>

    <h1 class="page-title mt-2">Undang anggota</h1>
    <p class="mt-1 max-w-2xl text-[14px] leading-6 text-muted">
        Undangan berupa tautan sekali pakai yang berlaku 7 hari. Email penerima terkunci dan tidak dapat diubah.
    </p>

    @if($flash)
        <div class="mt-4 rounded-[4px] border border-present/40 bg-present/5 px-4 py-3 text-[13px] text-present" role="status">
            {{ $flash }}
        </div>
    @endif

    @if($inviteLink)
        <x-panel class="mt-4" title="Tautan undangan" description="Salin dan kirim melalui email atau pesan internal.">
            <div class="panel-pad">
                <div class="flex flex-col gap-2 sm:flex-row">
                    <input type="text" readonly value="{{ $inviteLink }}" class="field-input font-mono text-[12px]" aria-label="Tautan undangan">
                    <button type="button" class="btn btn-secondary btn-sm shrink-0" onclick="navigator.clipboard.writeText('{{ $inviteLink }}'); this.textContent='Tersalin'">Salin</button>
                </div>
                <p class="mt-2 text-[12px] text-muted">Tautan ini hanya ditampilkan sekali. Bila hilang, buat undangan baru.</p>
            </div>
        </x-panel>
    @endif

    <form wire:submit="send" class="mt-5 grid gap-4 lg:grid-cols-[1fr_320px]">
        <x-panel title="Data undangan">
            <div class="panel-pad grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-field name="email" label="Email" type="email" wire:model="email" required placeholder="nama@perusahaan.id" help="Email ini akan terkunci pada undangan." />
                </div>
                <x-select name="role" label="Peran" wire:model.live="role" required :options="$roles" />
                <x-select name="primary_location_id" label="Lokasi utama" wire:model="primary_location_id" placeholder="Pilih lokasi" :options="$locations->pluck('name', 'id')->all()" help="Wajib untuk peninjau." />
                <x-select name="division_id" label="Divisi" wire:model="division_id" placeholder="Tanpa divisi" :options="$divisions->pluck('name', 'id')->all()" />
            </div>
        </x-panel>

        <x-panel title="Ringkasan">
            <div class="panel-pad space-y-3">
                <p class="text-[13px] leading-6 text-muted">
                    Anggota yang menerima undangan akan membuat kata sandi sendiri. Bila email sudah terdaftar, ia harus masuk terlebih dahulu.
                </p>
                <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled" wire:target="send">
                    <span wire:loading.remove wire:target="send">Buat undangan</span>
                    <span wire:loading wire:target="send">Membuat...</span>
                </button>
            </div>
        </x-panel>
    </form>
</div>
