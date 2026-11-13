<div>
    <h1 class="page-title">Profil</h1>
    <p class="mt-1 max-w-2xl text-[14px] leading-6 text-muted">
        Nama dan email dipakai pada catatan presensi serta undangan. Email harus unik.
    </p>

    @if($flash)
        <div class="mt-4 rounded-[4px] border border-present/40 bg-present/5 px-4 py-3 text-[13px] text-present" role="status">{{ $flash }}</div>
    @endif

    <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_320px]">
        <div class="space-y-4">
            <x-panel title="Data diri">
                <form wire:submit="save" class="panel-pad grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-field name="name" label="Nama lengkap" wire:model="name" required />
                    </div>
                    <div class="sm:col-span-2">
                        <x-field name="email" label="Email" type="email" wire:model="email" required />
                    </div>
                    <div class="sm:col-span-2" x-show="$wire.email.toLowerCase() !== $wire.original_email.toLowerCase()">
                        <x-password-field
                            name="current_password"
                            label="Kata sandi saat ini untuk konfirmasi"
                            wire:model="current_password"
                            autocomplete="current-password"
                        />
                        <p class="mt-1 text-[12px] text-muted">Perubahan email memerlukan konfirmasi kata sandi saat ini.</p>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">Simpan profil</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </x-panel>

            <x-panel title="Kata sandi" description="Gunakan minimal 8 karakter, berisi huruf dan angka.">
                @if($passwordFlash)
                    <div class="mx-4 mt-4 rounded-[4px] border border-present/40 bg-present/5 px-4 py-3 text-[13px] text-present md:mx-6" role="status">{{ $passwordFlash }}</div>
                @endif
                <form wire:submit="updatePassword" class="panel-pad grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-password-field name="current_password" label="Kata sandi saat ini" wire:model="current_password" required autocomplete="current-password" />
                    </div>
                    <x-password-field name="password" label="Kata sandi baru" wire:model="password" required autocomplete="new-password" />
                    <x-password-field name="password_confirmation" label="Ulangi kata sandi baru" wire:model="password_confirmation" required autocomplete="new-password" />
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn btn-secondary" wire:loading.attr="disabled" wire:target="updatePassword">
                            <span wire:loading.remove wire:target="updatePassword">Ubah kata sandi</span>
                            <span wire:loading wire:target="updatePassword">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </x-panel>
        </div>

        <x-panel title="Keanggotaan">
            <dl class="divide-y divide-divider text-[13px]">
                <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                    <dt class="text-muted">Organisasi</dt>
                    <dd class="text-right">{{ $tenant->name }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                    <dt class="text-muted">Peran</dt>
                    <dd>{{ $membership->role->label() }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                    <dt class="text-muted">Zona waktu</dt>
                    <dd>{{ $tenant->timezone }}</dd>
                </div>
            </dl>
        </x-panel>
    </div>
</div>
