<?php

namespace App\Http\Middleware;

use App\Enums\MembershipRole;
use App\Enums\TenantStatus;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantActive
{
    public function __construct(protected TenantContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->context->tenant();

        if (! $tenant) {
            return redirect()->route('tenant.status');
        }

        if ($tenant->status !== TenantStatus::Active) {
            $membership = $this->context->membership();

            // Pemilik dan anggota sama-sama diarahkan ke halaman status.
            // Anggota menerima pesan berbeda dari pemilik (ARCHITECTURE 5.2).
            $message = $membership?->role === MembershipRole::Owner
                ? 'Organisasi ini belum aktif.'
                : ($tenant->status === TenantStatus::Suspended
                    ? 'Organisasi ini sedang ditangguhkan.'
                    : 'Organisasi ini belum aktif. Anda dapat masuk setelah organisasi diaktifkan.');

            return redirect()->route('tenant.status')->withErrors(['status' => $message]);
        }

        return $next($request);
    }
}
