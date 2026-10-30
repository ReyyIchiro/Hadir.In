<div>
    <a href="{{ route('platform.tenants.index') }}" class="link inline-flex items-center gap-1 text-[13px]">
        <x-icon name="chevron-left" :size="14" />
        <span>Kembali ke organisasi</span>
    </a>

    <div class="mt-2 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="page-title">{{ $tenant->name }}</h1>
            <p class="mt-1 text-[13px] text-muted">{{ $tenant->slug }}</p>
        </div>
        <x-status :status="$tenant->status->value" :label="$tenant->status->label()" />
    </div>

    @if(session('status'))
        <div class="mt-4 rounded-[4px] border border-present/40 bg-present/5 px-4 py-3 text-[13px] text-present" role="status">
            {{ session('status') }}
        </div>
    @endif

    <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_320px]">
        <x-panel title="Data organisasi" description="Hanya data administratif. Tidak ada data presensi atau anggota.">
            <dl class="divide-y divide-divider text-[13px]">
                @foreach([
                    'Nama' => $tenant->name,
                    'Slug' => $tenant->slug,
                    'Zona waktu' => $tenant->timezone,
                    'Status' => $tenant->status->label(),
                    'Paket' => $tenant->plan ? ucfirst($tenant->plan) : '--',
                    'Dibuat' => \App\Support\Waktu::dateLong($tenant->created_at),
                    'Anggota aktif' => (string) $activeMembers,
                    'Undangan tertunda' => (string) $pendingInvitations,
                ] as $label => $value)
                    <div class="flex items-center justify-between gap-4 px-4 py-3 md:px-6">
                        <dt class="text-muted">{{ $label }}</dt>
                        <dd class="text-right">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-panel>

        <x-panel title="Tindakan status" description="Perubahan status berlaku segera.">
            <div class="panel-pad space-y-3">
                @if($tenant->status === \App\Enums\TenantStatus::Pending)
                    <form method="POST" action="{{ route('platform.tenants.activate', $tenant) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full">Aktifkan organisasi</button>
                    </form>
                @elseif($tenant->status === \App\Enums\TenantStatus::Active)
                    <form method="POST" action="{{ route('platform.tenants.suspend', $tenant) }}" onsubmit="return confirm('Tangguhkan organisasi ini? Anggota tidak dapat masuk sampai diaktifkan kembali.')">
                        @csrf
                        <button type="submit" class="btn btn-secondary w-full">Tangguhkan organisasi</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('platform.tenants.restore', $tenant) }}" onsubmit="return confirm('Aktifkan kembali organisasi ini?')">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full">Aktifkan kembali</button>
                    </form>
                @endif

                <p class="text-[12px] leading-5 text-muted">
                    Organisasi tidak dapat dihapus. Penangguhan menghentikan akses anggota tanpa menghapus data.
                </p>
            </div>
        </x-panel>
    </div>
</div>
