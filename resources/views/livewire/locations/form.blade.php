<div>
    <a href="{{ route('app.locations.index') }}" class="link inline-flex items-center gap-1 text-[13px]">
        <x-icon name="chevron-left" :size="14" />
        <span>Kembali ke lokasi</span>
    </a>

    <h1 class="page-title mt-2">{{ $location ? 'Ubah lokasi' : 'Tambah lokasi' }}</h1>
    <p class="mt-1 max-w-2xl text-[14px] leading-6 text-muted">
        Tentukan titik pusat dan radius. Anggota hanya bisa absen di dalam radius ini, ditambah buffer dari pengaturan organisasi.
    </p>

    <form wire:submit="save" class="mt-5 grid gap-4 lg:grid-cols-[1fr_340px]">
        <div class="space-y-4">
            <x-panel title="Detail lokasi">
                <div class="panel-pad grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-field name="name" label="Nama lokasi" wire:model="name" required placeholder="Contoh: Kantor Pusat" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-field name="address" label="Alamat" wire:model="address" placeholder="Jalan, nomor, kota" />
                    </div>
                    <x-field name="latitude" label="Latitude" wire:model="latitude" required placeholder="-6.2088" help="Format desimal, contoh -6.2088." />
                    <x-field name="longitude" label="Longitude" wire:model="longitude" required placeholder="106.8456" help="Format desimal, contoh 106.8456." />
                    <x-field name="radius_meters" label="Radius (meter)" type="number" wire:model="radius_meters" required help="Antara 20 dan 5000 meter." />
                    <div class="flex items-end pb-2">
                        <label class="flex items-center gap-2 text-[13px]">
                            <input type="checkbox" wire:model="is_active" class="h-4 w-4 rounded-[2px] border-divider text-stamp focus:ring-focus">
                            <span>Lokasi aktif</span>
                        </label>
                    </div>
                </div>
            </x-panel>

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Simpan lokasi</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
                <a href="{{ route('app.locations.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </div>

        <x-panel title="Peta" description="Klik peta untuk mengisi koordinat.">
            <div class="panel-pad">
                <div
                    x-data="{
                        map: null,
                        marker: null,
                        circle: null,
                        lat: @entangle('latitude'),
                        lng: @entangle('longitude'),
                        radius: @entangle('radius_meters'),
                        init() {
                            this.$nextTick(() => this.boot());
                        },
                        boot() {
                            if (! window.L || this.map) return;
                            const start = [parseFloat(this.lat) || -6.2088, parseFloat(this.lng) || 106.8456];
                            this.map = window.L.map(this.$refs.peta, { scrollWheelZoom: false }).setView(start, 15);
                            window.L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; OpenStreetMap'
                            }).addTo(this.map);
                            this.paint();
                            this.map.on('click', (event) => {
                                this.lat = event.latlng.lat.toFixed(7);
                                this.lng = event.latlng.lng.toFixed(7);
                                this.paint();
                            });
                        },
                        paint() {
                            if (! this.map) return;
                            const lat = parseFloat(this.lat) || -6.2088;
                            const lng = parseFloat(this.lng) || 106.8456;
                            if (this.marker) this.marker.remove();
                            if (this.circle) this.circle.remove();
                            this.marker = window.L.marker([lat, lng]).addTo(this.map);
                            this.circle = window.L.circle([lat, lng], {
                                radius: parseInt(this.radius) || 100,
                                color: '#A63A2B',
                                weight: 1.5,
                                fillOpacity: 0.06
                            }).addTo(this.map);
                            this.map.setView([lat, lng]);
                        }
                    }"
                    x-init="init()"
                >
                    <div x-ref="peta" class="h-[260px] w-full rounded-[2px] border border-divider bg-tint"></div>
                    <p class="mt-2 text-[12px] leading-5 text-muted">Peta memuat ubin OpenStreetMap. Titik dan radius diperbarui saat Anda mengubah angka.</p>
                </div>
            </div>
        </x-panel>
    </form>
</div>
