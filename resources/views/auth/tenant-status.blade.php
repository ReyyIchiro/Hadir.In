<x-layouts.auth title="Status organisasi">
    <div class="panel panel-pad">
        <span class="stamp {{ $tenant->status->value === 'active' ? '' : 'stamp-neutral' }}">
            {{ $tenant->status->label() }}
        </span>

        <h1 class="mt-5 title-lg">
            {{ $tenant->name }}
        </h1>

        @if($tenant->status->value === 'pending')
            <p class="mt-3 text-[14px] leading-6 text-muted">
                Organisasi Anda sedang menunggu aktivasi. Kami akan mengaktifkan setelah data
                organisasi diperiksa. Anda dapat menyiapkan lokasi dan anggota setelah aktif.
            </p>
        @elseif($tenant->status->value === 'suspended')
            <p class="mt-3 text-[14px] leading-6 text-muted">
                Akses organisasi ini sedang ditangguhkan. Hubungi kami untuk memulihkan akses.
            </p>
        @else
            <p class="mt-3 text-[14px] leading-6 text-muted">Organisasi Anda sudah aktif.</p>
            <a href="{{ route('app.home') }}" class="btn btn-primary mt-6">Buka aplikasi</a>
        @endif

        <dl class="mt-6 grid grid-cols-2 gap-4 border-t border-divider pt-4 text-[13px]">
            <div>
                <dt class="text-muted">Zona waktu</dt>
                <dd class="mt-0.5">{{ $tenant->timezone }}</dd>
            </div>
            <div>
                <dt class="text-muted">Peran Anda</dt>
                <dd class="mt-0.5">{{ $membership->role->label() }}</dd>
            </div>
            <div>
                <dt class="text-muted">Dibuat</dt>
                <dd class="mt-0.5">{{ \App\Support\Waktu::dateLong($tenant->created_at) }}</dd>
            </div>
            <div>
                <dt class="text-muted">Slug</dt>
                <dd class="mt-0.5 font-mono text-[12px]">{{ $tenant->slug }}</dd>
            </div>
        </dl>
    </div>

    @if($otherMemberships->isNotEmpty())
        <div class="panel panel-pad mt-4">
            <h2 class="title-md">Berpindah organisasi</h2>
            <p class="mt-1 text-[13px] leading-5 text-muted">
                Anda juga menjadi anggota organisasi berikut. Pilih organisasi untuk melanjutkan.
            </p>
            <div class="mt-4 divide-y divide-divider">
                @foreach($otherMemberships as $other)
                    <form method="POST" action="{{ route('app.tenants.switch', $other->tenant) }}"
                          class="flex items-center justify-between gap-3 py-3">
                        @csrf
                        <div class="min-w-0">
                            <p class="truncate text-[14px]">{{ $other->tenant->name }}</p>
                            <p class="mt-0.5 text-[12px] text-muted">{{ $other->role->label() }}</p>
                        </div>
                        <button type="submit" class="btn btn-secondary btn-sm">Pindah ke sini</button>
                    </form>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">
        @csrf
        <button type="submit" class="text-[13px] text-muted hover:text-text">Keluar dari akun</button>
    </form>
</x-layouts.auth>
