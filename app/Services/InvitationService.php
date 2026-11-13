<?php

namespace App\Services;

use App\Enums\MembershipRole;
use App\Mail\SendInvitationEmail;
use App\Models\Division;
use App\Models\Invitation;
use App\Models\Location;
use App\Models\Membership;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Waktu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationService
{
    public function create(Tenant $tenant, array $data, User $inviter): array
    {
        $role = MembershipRole::from($data['role']);

        if ($role === MembershipRole::Owner) {
            throw ValidationException::withMessages(['role' => 'Peran pemilik tidak dapat diundang.']);
        }

        $inviterMembership = $inviter->membershipFor($tenant);

        if (! $inviterMembership?->role->canManageMembers()) {
            abort(403);
        }

        if ($inviterMembership->role === MembershipRole::Admin && $role === MembershipRole::Admin) {
            throw ValidationException::withMessages(['role' => 'Admin tidak dapat mengundang admin lain.']);
        }

        if ($role === MembershipRole::Reviewer && empty($data['primary_location_id'])) {
            throw ValidationException::withMessages(['primary_location_id' => 'Peninjau wajib memiliki lokasi utama.']);
        }

        // Komponen sudah memvalidasi lewat Rule::exists. Pemeriksaan kedua
        // menjaga pemanggil lain dari id lintas organisasi (BR-80).
        if (! empty($data['primary_location_id'])
            && ! Location::whereKey($data['primary_location_id'])->exists()) {
            throw ValidationException::withMessages(['primary_location_id' => 'Lokasi utama tidak ditemukan di organisasi ini.']);
        }

        if (! empty($data['division_id'])
            && ! Division::whereKey($data['division_id'])->exists()) {
            throw ValidationException::withMessages(['division_id' => 'Divisi tidak ditemukan di organisasi ini.']);
        }

        $email = mb_strtolower(trim($data['email']));

        $existingPending = Invitation::where('tenant_id', $tenant->id)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->exists();

        if ($existingPending) {
            throw ValidationException::withMessages(['email' => 'Sudah ada undangan aktif untuk email ini.']);
        }

        if ($tenant->memberships()->whereHas('user', fn ($q) => $q->where('email', $email))->exists()) {
            throw ValidationException::withMessages(['email' => 'Email ini sudah menjadi anggota organisasi.']);
        }

        $token = Str::random(64);

        $invitation = Invitation::create([
            'tenant_id' => $tenant->id,
            'email' => $email,
            'role' => $role,
            'primary_location_id' => $data['primary_location_id'] ?? null,
            'division_id' => $data['division_id'] ?? null,
            'token_hash' => hash('sha256', $token),
            'invited_by_id' => $inviter->id,
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($email)->queue(new SendInvitationEmail(
            $tenant->name,
            $role->label(),
            $inviter->name,
            Waktu::dateLong($invitation->expires_at->toDateString()),
            route('invitations.show', $token),
        ));

        return [$invitation, $token];
    }

    public function findByToken(string $token): ?Invitation
    {
        return Invitation::withoutGlobalScopes()
            ->where('token_hash', hash('sha256', $token))
            ->with(['tenant', 'primaryLocation', 'division'])
            ->first();
    }

    public function accept(Invitation $invitation, ?User $user, ?string $name = null, ?string $password = null): User
    {
        return DB::transaction(function () use ($invitation, $user, $name, $password) {
            // Kunci baris undangan agar dua permintaan bersamaan tidak
            // menerimanya sekaligus. Status tanpa kunci hanya cek awal.
            $locked = Invitation::withoutGlobalScopes()
                ->whereKey($invitation->id)
                ->lockForUpdate()
                ->first();

            if (! $locked?->isPending()) {
                throw ValidationException::withMessages(['token' => 'Undangan ini sudah tidak berlaku.']);
            }

            $invitation = $locked;

            if ($user) {
                if (mb_strtolower($user->email) !== $invitation->email) {
                    throw ValidationException::withMessages([
                        'email' => 'Undangan ini ditujukan untuk alamat email lain.',
                    ]);
                }
            } else {
                if (User::where('email', $invitation->email)->exists()) {
                    throw ValidationException::withMessages([
                        'email' => 'Email ini sudah terdaftar. Masuk terlebih dahulu untuk menerima undangan.',
                    ]);
                }

                $user = User::create([
                    'name' => $name,
                    'email' => $invitation->email,
                    'password' => $password,
                ]);
            }

            Membership::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $invitation->tenant_id, 'user_id' => $user->id],
                [
                    'role' => $invitation->role,
                    'primary_location_id' => $invitation->primary_location_id,
                    'division_id' => $invitation->division_id,
                    'is_active' => true,
                    'joined_at' => now(),
                ]
            );

            $invitation->update([
                'accepted_at' => now(),
                'accepted_by_id' => $user->id,
            ]);

            return $user;
        });
    }

    public function revoke(Invitation $invitation): void
    {
        $invitation->update(['revoked_at' => now()]);
    }
}
