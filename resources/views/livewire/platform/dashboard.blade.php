<div>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="eyebrow">Platform</p>
            <h1 class="page-title mt-1">Ringkasan platform</h1>
        </div>
        <a href="{{ route('platform.tenants.index') }}" class="btn btn-secondary btn-sm">Kelola organisasi</a>
    </div>

    <div class="mt-6 grid grid-cols-2 border-y border-divider sm:grid-cols-4">
        @foreach([
            'total' => 'Organisasi',
            'pending' => 'Menunggu aktivasi',
            'active' => 'Aktif',
            'suspended' => 'Ditangguhkan',
        ] as $key => $label)
            <div class="border-b border-divider px-4 py-4 sm:border-b-0 sm:border-r sm:last:border-r-0 md:px-5">
                <p class="text-[11px] uppercase tracking-[0.08em] text-muted">{{ $label }}</p>
                <p class="mt-1 font-display text-[28px] font-semibold tabular-nums leading-none">{{ $counts[$key] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-[1fr_320px]">
        <x-panel title="Organisasi terbaru">
            @if($recent->isEmpty())
                <x-empty-state title="Belum ada organisasi" description="Organisasi yang mendaftar akan muncul di sini." />
            @else
                <div class="table-scroll">
                    <table class="ledger">
                        <caption>Organisasi terbaru</caption>
                        <thead>
                            <tr>
                                <th scope="col">Nama</th>
                                <th scope="col">Status</th>
                                <th scope="col">Dibuat</th>
                                <th scope="col"><span class="sr-only">Tindakan</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent as $tenant)
                                <tr>
                                    <td>
                                        <a href="{{ route('platform.tenants.show', $tenant) }}" class="link">{{ $tenant->name }}</a>
                                        <p class="text-[11px] text-muted">{{ $tenant->slug }}</p>
                                    </td>
                                    <td><x-status :status="$tenant->status->value" :label="$tenant->status->label()" /></td>
                                    <td class="tabular-nums text-muted">{{ \App\Support\Waktu::dateShort($tenant->created_at) }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('platform.tenants.show', $tenant) }}" class="link text-[12px]">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-panel>

        <x-panel title="Data platform">
            <dl class="divide-y divide-divider text-[13px]">
                <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                    <dt class="text-muted">Anggota aktif</dt>
                    <dd class="tabular-nums">{{ $members }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3 px-4 py-3 md:px-6">
                    <dt class="text-muted">Undangan tertunda</dt>
                    <dd class="tabular-nums">{{ $pendingInvitations }}</dd>
                </div>
            </dl>
            <div class="border-t border-divider px-4 py-3 md:px-6">
                <p class="text-[12px] leading-5 text-muted">
                    Platform admin hanya melihat data organisasi. Presensi, selfie, dan daftar anggota tidak dapat diakses dari sini.
                </p>
            </div>
        </x-panel>
    </div>
</div>
