<div
    x-data="{
        lat: @entangle('latitude'),
        lng: @entangle('longitude'),
        acc: @entangle('accuracy'),
        status: 'idle',
        message: '',
        map: null,
        marker: null,
        accuracyCircle: null,
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
                    this.message = 'Lokasi terkunci. Jarak ke lokasi kerja dihitung otomatis.';
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
                this.map = window.L.map(this.$refs.map, { zoomControl: true, attributionControl: true }).setView([lat, lng], 16);
                window.L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(this.map);
            } else {
                this.map.setView([lat, lng], 16);
            }

            if (this.marker) this.marker.remove();
            if (this.accuracyCircle) this.accuracyCircle.remove();

            this.marker = window.L.marker([lat, lng]).addTo(this.map);
            this.accuracyCircle = window.L.circle([lat, lng], { radius: accuracy, weight: 1 }).addTo(this.map);

            this.locations.forEach((loc) => {
                window.L.circle([loc.lat, loc.lng], {
                    radius: loc.radius,
                    color: '#A63A2B',
                    weight: 1.5,
                    fillOpacity: 0.06,
                }).addTo(this.map);
            });
        }
    }"
    x-init="capture()"
    class="grid gap-4 lg:grid-cols-[1.1fr_1fr]"
>
    <x-panel title="Lokasi Anda" description="Peta hanya dipakai untuk memastikan posisi, bukan pelacakan.">
        <div class="panel-pad">
            <div x-show="status === 'ready'" wire:ignore x-ref="map" class="h-[280px] w-full rounded-[4px] border border-divider bg-tint" role="img" aria-label="Peta lokasi kerja"></div>

            <div x-show="status !== 'ready'" class="flex h-[280px] w-full flex-col items-center justify-center gap-2 rounded-[4px] border border-divider bg-tint px-6 text-center">
                <x-icon name="map-pin" :size="22" class="text-muted" />
                <p class="text-[13px] leading-5 text-muted" x-text="status === 'locating' ? 'Menunggu izin dan titik GPS.' : 'Peta muncul setelah lokasi terbaca.'"></p>
            </div>

            <p class="mt-3 text-[13px] leading-5 text-muted" x-text="message"></p>

            <dl class="mt-4 grid grid-cols-3 gap-3 border-t border-divider pt-3 text-[13px]">
                <div>
                    <dt class="text-[11px] uppercase tracking-[0.06em] text-muted">Latitude</dt>
                    <dd class="mt-0.5 font-mono text-[13px] tabular-nums" x-text="lat === null ? '--' : Number(lat).toFixed(5)"></dd>
                </div>
                <div>
                    <dt class="text-[11px] uppercase tracking-[0.06em] text-muted">Longitude</dt>
                    <dd class="mt-0.5 font-mono text-[13px] tabular-nums" x-text="lng === null ? '--' : Number(lng).toFixed(5)"></dd>
                </div>
                <div>
                    <dt class="text-[11px] uppercase tracking-[0.06em] text-muted">Akurasi</dt>
                    <dd class="mt-0.5 font-mono text-[13px] tabular-nums" x-text="acc === null ? '--' : Math.round(acc) + ' m'"></dd>
                </div>
            </dl>

            <div class="mt-4 flex flex-wrap gap-2">
                <button type="button" class="btn btn-secondary btn-sm" @click="capture()" :disabled="status === 'locating'">
                    <x-icon name="map-pin" :size="16" />
                    <span x-text="status === 'locating' ? 'Mencari lokasi...' : 'Perbarui lokasi'"></span>
                </button>
                @if($locations->isNotEmpty())
                    <span class="text-[12px] leading-5 text-muted">{{ $locations->count() }} lokasi kerja aktif</span>
                @endif
            </div>
        </div>
    </x-panel>

    <div class="space-y-4">
        <x-panel title="Selfie" :description="$settings->require_selfie ? 'Selfie wajib diambil setiap absen masuk.' : 'Selfie tidak diwajibkan organisasi ini.'">
            <div class="panel-pad">
                <input
                    type="file"
                    accept="image/*"
                    capture="user"
                    wire:model="selfie"
                    id="selfie"
                    class="sr-only"
                >
                <label for="selfie" class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-[4px] border border-dashed border-divider bg-tint px-4 py-8 text-center hover:border-stamp">
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
                    <p class="field-error mt-2">{{ $message }}</p>
                @enderror
                <div wire:loading wire:target="selfie" class="mt-2 text-[12px] text-muted">Memproses gambar...</div>
            </div>
        </x-panel>

        @if($flash)
            <div class="rounded-[4px] border px-4 py-3 text-[13px] leading-6 {{ $flashTone === 'success' ? 'border-present/40 bg-present/5 text-present' : ($flashTone === 'danger' ? 'border-absent/40 bg-absent/5 text-absent' : 'border-divider bg-surface text-muted') }}" role="status">
                {{ $flash }}
                @if($canRequestReview)
                    <button type="button" wire:click="requestReview" class="link mt-1 block text-[13px]">Ajukan tinjauan</button>
                @endif
            </div>
        @endif

        <div class="flex flex-col gap-2">
            <button
                type="button"
                wire:click="submit"
                wire:loading.attr="disabled"
                wire:target="submit"
                class="btn btn-primary btn-lg w-full"
            >
                <x-icon name="check" :size="18" />
                <span wire:loading.remove wire:target="submit">Absen masuk sekarang</span>
                <span wire:loading wire:target="submit">Menyimpan...</span>
            </button>
            <p class="text-center text-[12px] leading-5 text-muted">
                Waktu dicatat dari server, bukan dari jam perangkat.
            </p>
        </div>
    </div>
</div>
