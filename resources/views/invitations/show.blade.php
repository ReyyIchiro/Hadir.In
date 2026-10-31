<x-layouts.auth title="Undangan bergabung">
    <div class="mx-auto w-full max-w-[440px]">
        <p class="eyebrow">Undangan</p>
        <h1 class="mt-2 title-lg">
            Bergabung dengan {{ $invitation->tenant->name }}
        </h1>
        <p class="mt-2 text-[14px] leading-6 text-muted">
            Anda diundang sebagai {{ $invitation->role->label() }}. Undangan berlaku sampai {{ $invitation->expires_at->translatedFormat('j F Y') }}.
        </p>

        <dl class="mt-6 divide-y divide-divider border-y border-divider text-[14px]">
            <div class="flex items-center justify-between gap-4 py-3">
                <dt class="text-muted">Email</dt>
                <dd class="font-medium">{{ $invitation->email }}</dd>
            </div>
            <div class="flex items-center justify-between gap-4 py-3">
                <dt class="text-muted">Peran</dt>
                <dd class="font-medium">{{ $invitation->role->label() }}</dd>
            </div>
            @if($invitation->primaryLocation)
                <div class="flex items-center justify-between gap-4 py-3">
                    <dt class="text-muted">Lokasi utama</dt>
                    <dd class="font-medium">{{ $invitation->primaryLocation->name }}</dd>
                </div>
            @endif
            @if($invitation->division)
                <div class="flex items-center justify-between gap-4 py-3">
                    <dt class="text-muted">Divisi</dt>
                    <dd class="font-medium">{{ $invitation->division->name }}</dd>
                </div>
            @endif
        </dl>

        @if($existingUser)
            <div class="mt-6 rounded-[4px] border-[1.5px] border-divider bg-tint p-4">
                <p class="text-[14px] leading-6">
                    Email ini sudah terdaftar. Masuk dengan akun tersebut untuk menerima undangan.
                </p>
            </div>
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg mt-4 w-full">Masuk untuk menerima</a>
        @else
            <form method="POST" action="{{ route('invitations.accept', $token) }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="name" class="field-label">Nama lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name" class="field-input" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                    @error('name')
                        <p id="name-error" class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <x-password-field name="password" label="Kata sandi" required autocomplete="new-password" help="Minimal 8 karakter, berisi huruf dan angka." />
                <x-password-field name="password_confirmation" label="Ulangi kata sandi" required autocomplete="new-password" />

                <button type="submit" class="btn btn-primary btn-lg w-full">Terima undangan</button>
            </form>
        @endif
    </div>
</x-layouts.auth>
