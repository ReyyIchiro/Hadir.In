<div
    x-data="{
        lat: @entangle('latitude'),
        lng: @entangle('longitude'),
        acc: @entangle('accuracy'),
        status: 'idle',
        message: '',
        map: null,
        marker: null,
        watchId: null,
        locations: @js($locations),
        timeout: {{ (int) $settings->gps_timeout_seconds }} * 1000,
        capture() {
            this.status = 'locating';
            this.message = 'Mencari lokasi Anda.';

            if (! navigator.geolocation) {
                this.status = 'error';
                this.message = 'Perangkat ini tidak mendukung GPS.';
                return;
            }

            const timer = setTimeout(() => {
                if (this.status === 'locating') {
                    this.status = 'error';
                    this.message = 'GPS tidak merespons. Coba di area terbuka.';
                }
            }, this.timeout);

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    clearTimeout(timer);
                    this.lat = position.coords.latitude;
                    this.lng = position.coords.longitude;
                    this.acc = position.coords.accuracy;
                    this.status = 'ready';
                    this.message = 'Lokasi terkunci.';
                    this.$nextTick(() => this.draw(position.coords.latitude, position.coords.longitude, position.coords.accuracy));
                },
                (error) => {
                    clearTimeout(timer);
                    this.status = 'error';
                    this.message = error.code === 1
                        ? 'Izin lokasi ditolak. Aktifkan izin lokasi lalu coba lagi.'
                        : 'Lokasi tidak tersedia. Coba lagi di area terbuka.';
                },
                { enableHighAccuracy: true, timeout: this.timeout, maximumAge: 0 }
            );
        },
        async draw(lat, lng, accuracy) {
            await window.hadirLoadMap();

            if (! this.map) {
                this.map = window.L.map(this.$refs.map, { keyboard: false }).setView([lat, lng], 16);
                window.L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(this.map);
            } else {
                this.map.setView([lat, lng], 16);
            }

            if (this.marker) this.marker.remove();
            this.marker = window.L.marker([lat, lng]).addTo(this.map);
            window.L.circle([lat, lng], { radius: accuracy, weight: 1 }).addTo(this.map);

            this.locations.forEach((loc) => {
                window.L.circle([loc.lat, loc.lng], {
                    radius: loc.radius,
                    color: '#b23a26',
                    weight: 1.5,
                    fillOpacity: 0.08,
                }).addTo(this.map);
            });
        }
    }"
    class="grid gap-4 lg:grid-cols-[1.1fr_1fr]"
>
    <x-panel title="Lokasi Anda" description="Pastikan posisi sudah sesuai sebelum absen pulang.">
        <div class="panel-pad">
            <div x-show="status === 'ready'" wire:ignore x-ref="map" class="h-[320px] w-full rounded-[4px] border border-divider bg-tint" role="img" aria-label="Peta lokasi kerja"></div>

            {{-- Izin lokasi diminta setelah pengguna menekan tombol, bukan
                 langsung saat halaman dibuka (DESIGN.md bagian Peta). --}}
            <div x-show="status !== 'ready'" class="flex h-[320px] w-full flex-col items-center justify-center gap-3 rounded-[4px] border border-divider bg-tint px-6 text-center">
                <x-icon name="map-pin" :size="22" class="text-muted" />
                <p class="text-[13px] leading-5 text-muted" x-text="status === 'locating' ? 'Menunggu izin dan titik GPS.' : 'Peta muncul setelah lokasi terbaca.'"></p>
                <button type="button" class="btn btn-primary btn-sm" x-show="status !== 'locating'" @click="capture()">
                    <span x-text="status === 'error' ? 'Coba lagi' : 'Aktifkan lokasi'"></span>
                </button>
            </div>

            <p class="mt-3 text-[13px] leading-5 text-muted" x-text="message"></p>

            <dl class="mt-4 grid grid-cols-3 gap-3 border-t border-divider pt-3 text-[13px]">
                <div>
                    <dt class="text-[11px] uppercase tracking-[0.06em] text-muted">Latitude</dt>
                    <dd class="mt-0.5 font-mono tabular-nums" x-text="lat === null ? '--' : Number(lat).toFixed(5)"></dd>
                </div>
                <div>
                    <dt class="text-[11px] uppercase tracking-[0.06em] text-muted">Longitude</dt>
                    <dd class="mt-0.5 font-mono tabular-nums" x-text="lng === null ? '--' : Number(lng).toFixed(5)"></dd>
                </div>
                <div>
                    <dt class="text-[11px] uppercase tracking-[0.06em] text-muted">Akurasi</dt>
                    <dd class="mt-0.5 font-mono tabular-nums" x-text="acc === null ? '--' : Math.round(acc) + ' m'"></dd>
                </div>
            </dl>

            <button type="button" class="btn btn-secondary btn-sm mt-4" @click="capture()" :disabled="status === 'locating'">
                <x-icon name="map-pin" :size="16" />
                <span x-text="status === 'locating' ? 'Mencari lokasi...' : 'Perbarui lokasi'"></span>
            </button>
        </div>
    </x-panel>

    <div class="space-y-4">
        @if($day)
            <x-panel title="Ringkasan hari ini">
                <dl class="divide-y divide-divider">
                    <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                        <dt class="text-[13px] text-muted">Absen masuk</dt>
                        <dd class="font-mono text-[16px] tabular-nums">{{ \App\Support\Waktu::time($day->check_in_at) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                        <dt class="text-[13px] text-muted">Status</dt>
                        <dd><x-status :status="$day->displayStatus()" /></dd>
                    </div>
                    @if($day->late_minutes > 0)
                        <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                            <dt class="text-[13px] text-muted">Terlambat</dt>
                            <dd class="font-mono text-[16px] tabular-nums">{{ $day->late_minutes }} menit</dd>
                        </div>
                    @endif
                </dl>
            </x-panel>
        @endif

        <x-panel title="Selfie pulang" :description="$settings->require_selfie ? 'Selfie wajib diambil.' : 'Selfie tidak diwajibkan organisasi ini.'">
            <div class="panel-pad">
                <input type="file" accept="image/*" capture="user" wire:model="selfie" id="selfie-out" class="sr-only" @if($errors->has('selfie')) aria-invalid="true" aria-describedby="selfie-out-error" @endif>
                <label for="selfie-out" class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-[4px] border border-dashed border-divider bg-tint px-4 py-8 text-center hover:border-stamp">
                    @if($selfie)
                        <img src="{{ $selfie->temporaryUrl() }}" alt="Pratinjau selfie" class="h-40 w-40 rounded-[4px] object-cover">
                        <span class="text-[13px] text-muted">Ketuk untuk ambil ulang</span>
                    @else
                        <x-icon name="camera" :size="28" class="text-muted" />
                        <span class="text-[14px] font-medium">Ambil selfie</span>
                        <span class="text-[12px] text-muted">Kamera depan, pencahayaan cukup</span>
                    @endif
                </label>
                @error('selfie')
                    <p id="selfie-out-error" class="field-error mt-2">{{ $message }}</p>
                @enderror
                <div wire:loading wire:target="selfie" class="mt-2 text-[12px] text-muted">Memproses gambar...</div>
            </div>
        </x-panel>

        @if($flash)
            <div class="rounded-[4px] border px-4 py-3 text-[13px] leading-6 {{ $flashTone === 'success' ? 'border-present/40 bg-present/5 text-present' : 'border-absent/40 bg-absent/5 text-absent' }}" role="status">
                {{ $flash }}
            </div>
        @endif

        <button type="button" wire:click="submit" wire:loading.attr="disabled" wire:target="submit" class="btn btn-primary btn-lg w-full">
            <x-icon name="check" :size="18" />
            <span wire:loading.remove wire:target="submit">Absen pulang sekarang</span>
            <span wire:loading wire:target="submit">Menyimpan...</span>
        </button>
    </div>
</div>
