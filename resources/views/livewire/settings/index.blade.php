<div>
    <h1 class="page-title">Pengaturan organisasi</h1>
    <p class="mt-1 max-w-2xl text-[14px] leading-6 text-muted">
        Pengaturan ini menjadi nilai bawaan untuk anggota yang tidak memiliki jadwal khusus.
    </p>

    @if($flash)
        <div class="mt-4 rounded-[4px] border border-present/40 bg-present/5 px-4 py-3 text-[13px] text-present" role="status">{{ $flash }}</div>
    @endif

    <form wire:submit="save" class="mt-5 space-y-4">
        <div class="grid gap-4 lg:grid-cols-2">
            <x-panel title="Jam kerja" description="Jam kerja bawaan dalam zona waktu organisasi ({{ $tenant->timezone }}).">
                <div class="panel-pad grid gap-4 sm:grid-cols-2">
                    <x-field name="work_start" label="Mulai kerja" type="time" wire:model="work_start" required />
                    <x-field name="work_end" label="Selesai kerja" type="time" wire:model="work_end" required />
                    <x-field name="break_start" label="Mulai istirahat" type="time" wire:model="break_start" />
                    <x-field name="break_end" label="Selesai istirahat" type="time" wire:model="break_end" />
                </div>
            </x-panel>

            <x-panel title="Jendela absen" description="Batas waktu yang dipakai saat memeriksa absen masuk dan pulang.">
                <div class="panel-pad grid gap-4 sm:grid-cols-2">
                    <x-field name="early_window_minutes" label="Buka absen lebih awal" type="number" wire:model="early_window_minutes" required help="Menit sebelum jam mulai." />
                    <x-field name="grace_minutes" label="Toleransi terlambat" type="number" wire:model="grace_minutes" required help="Menit setelah jam mulai." />
                    <x-field name="checkout_late_minutes" label="Batas absen pulang" type="number" wire:model="checkout_late_minutes" required help="Menit setelah jam selesai." />
                    <x-field name="early_leave_minutes" label="Toleransi pulang cepat" type="number" wire:model="early_leave_minutes" required help="Menit sebelum jam selesai." />
                </div>
            </x-panel>

            <x-panel title="Lokasi dan GPS" description="Dipakai untuk memeriksa posisi anggota saat absen.">
                <div class="panel-pad grid gap-4 sm:grid-cols-2">
                    <x-field name="geofence_buffer_meters" label="Buffer radius" type="number" wire:model="geofence_buffer_meters" required help="Toleransi di tepi radius lokasi." />
                    <x-field name="low_accuracy_meters" label="Batas akurasi rendah" type="number" wire:model="low_accuracy_meters" required help="Akurasi di atas nilai ini ditandai." />
                    <x-field name="gps_timeout_seconds" label="Batas tunggu GPS" type="number" wire:model="gps_timeout_seconds" required />
                    <div class="flex items-end pb-2">
                        <label class="flex items-center gap-2 text-[13px]">
                            <input type="checkbox" wire:model="require_location" class="h-4 w-4 rounded-[2px] border-divider text-stamp focus:ring-focus">
                            <span>Lokasi wajib saat absen</span>
                        </label>
                    </div>
                </div>
            </x-panel>

            <x-panel title="Selfie dan absen" description="Kebijakan verifikasi dan penyimpanan foto.">
                <div class="panel-pad grid gap-4 sm:grid-cols-2">
                    <x-field name="selfie_retention_days" label="Simpan selfie (hari)" type="number" wire:model="selfie_retention_days" required help="Setelah itu berkas dihapus, catatan tetap ada." />
                    <div class="space-y-2 pb-1">
                        <label class="flex items-center gap-2 text-[13px]">
                            <input type="checkbox" wire:model="require_selfie" class="h-4 w-4 rounded-[2px] border-divider text-stamp focus:ring-focus">
                            <span>Selfie wajib saat absen</span>
                        </label>
                        <label class="flex items-center gap-2 text-[13px]">
                            <input type="checkbox" wire:model="allow_unscheduled_attendance" class="h-4 w-4 rounded-[2px] border-divider text-stamp focus:ring-focus">
                            <span>Izinkan absen tanpa jadwal</span>
                        </label>
                    </div>
                </div>
            </x-panel>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Simpan pengaturan</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
            <p class="text-[12px] text-muted">Perubahan tidak mengubah catatan presensi yang sudah ada.</p>
        </div>
    </form>
</div>
