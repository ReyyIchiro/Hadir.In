<div>
    <a href="{{ route('app.schedules.index') }}" class="link inline-flex items-center gap-1 text-[13px]">
        <x-icon name="chevron-left" :size="14" />
        <span>Kembali ke jadwal</span>
    </a>

    <h1 class="page-title mt-2">Terapkan jadwal</h1>
    <p class="mt-1 max-w-2xl text-[14px] leading-6 text-muted">
        Buat jadwal untuk banyak anggota sekaligus. Pilih rentang tanggal dan hari dalam minggu, lalu periksa ringkasan sebelum menyimpan.
    </p>

    @if($flash)
        <div class="mt-4 rounded-[4px] border border-present/40 bg-present/5 px-4 py-3 text-[13px] leading-6 text-present" role="status">{{ $flash }}</div>
    @endif

    <form wire:submit="apply" class="mt-5 grid gap-4 lg:grid-cols-[1fr_320px]">
        <div class="space-y-4">
            <x-panel title="Anggota">
                <div class="panel-pad">
                    @error('userIds')<p class="field-error mb-2">{{ $message }}</p>@enderror
                    <div class="grid max-h-56 gap-1 overflow-y-auto sm:grid-cols-2">
                        @foreach($members as $member)
                            <label class="flex items-center gap-2 rounded-[2px] px-1 py-1 text-[13px] hover:bg-tint">
                                <input type="checkbox" wire:model.live="userIds" value="{{ $member->user_id }}" class="h-4 w-4 rounded-[2px] border-divider text-stamp focus:ring-stamp">
                                <span class="truncate">{{ $member->user->name }}</span>
                                <span class="ml-auto shrink-0 text-[11px] text-muted">{{ $member->role->label() }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </x-panel>

            <x-panel title="Rentang dan pola">
                <div class="panel-pad space-y-4">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-field name="startDate" label="Tanggal mulai" type="date" wire:model.live="startDate" required />
                        <x-field name="endDate" label="Tanggal selesai" type="date" wire:model.live="endDate" required help="Maksimal 92 hari." />
                    </div>

                    <fieldset>
                        <legend class="text-[13px] font-medium">Hari dalam minggu</legend>
                        @error('weekdays')<p class="field-error mt-1">{{ $message }}</p>@enderror
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach(\App\Support\Waktu::DAYS as $number => $label)
                                <label class="cursor-pointer">
                                    <input type="checkbox" wire:model.live="weekdays" value="{{ $number }}" class="peer sr-only">
                                    <span class="inline-flex rounded-[2px] border border-divider px-2.5 py-1 text-[12px] text-muted peer-checked:border-stamp peer-checked:bg-stamp-tint peer-checked:text-stamp">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-field name="startTime" label="Jam mulai" type="time" wire:model="startTime" required />
                        <x-field name="endTime" label="Jam selesai" type="time" wire:model="endTime" required />
                        <x-field name="breakStart" label="Istirahat mulai" type="time" wire:model="breakStart" />
                        <x-field name="breakEnd" label="Istirahat selesai" type="time" wire:model="breakEnd" />
                    </div>

                    <x-field name="note" label="Catatan jadwal" wire:model="note" help="Opsional, tampil di detail jadwal." />

                    <fieldset>
                        <legend class="text-[13px] font-medium">Bila jadwal sudah ada</legend>
                        <div class="mt-2 space-y-1.5">
                            <label class="flex items-start gap-2 text-[13px]">
                                <input type="radio" wire:model.live="mode" value="skip" class="mt-0.5 h-4 w-4 border-divider text-stamp focus:ring-stamp">
                                <span>Lewati yang sudah ada <span class="block text-[12px] text-muted">Jadwal lama tidak berubah.</span></span>
                            </label>
                            <label class="flex items-start gap-2 text-[13px]">
                                <input type="radio" wire:model.live="mode" value="overwrite" class="mt-0.5 h-4 w-4 border-divider text-stamp focus:ring-stamp">
                                <span>Timpa yang sudah ada <span class="block text-[12px] text-muted">Jadwal lama diganti dengan pengaturan ini.</span></span>
                            </label>
                        </div>
                    </fieldset>
                </div>
            </x-panel>
        </div>

        <div class="space-y-4">
            <x-panel title="Ringkasan">
                <div class="panel-pad">
                    @if($preview)
                        <dl class="space-y-2 text-[13px]">
                            <div class="flex items-center justify-between"><dt class="text-muted">Dibuat</dt><dd class="font-mono text-[20px] tabular-nums">{{ $preview['created'] }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="text-muted">Diperbarui</dt><dd class="font-mono text-[20px] tabular-nums">{{ $preview['updated'] }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="text-muted">Dilewati</dt><dd class="font-mono text-[20px] tabular-nums">{{ $preview['skipped'] }}</dd></div>
                        </dl>
                        <p class="mt-3 border-t border-divider pt-3 text-[12px] leading-5 text-muted">
                            Perkiraan dihitung dari pilihan saat ini. Angka akhir muncul setelah diterapkan.
                        </p>
                    @else
                        <p class="text-[13px] leading-6 text-muted">Pilih anggota dan rentang untuk melihat perkiraan.</p>
                    @endif

                    <button type="button" wire:click="preview" class="btn btn-ghost btn-sm mt-3 w-full">Perbarui ringkasan</button>
                </div>
            </x-panel>

            <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled" wire:target="apply">
                <span wire:loading.remove wire:target="apply">Terapkan jadwal</span>
                <span wire:loading wire:target="apply">Menerapkan...</span>
            </button>
        </div>
    </form>
</div>
