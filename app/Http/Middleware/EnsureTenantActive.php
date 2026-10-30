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

            if ($membership?->role === MembershipRole::Owner) {
                return redirect()->route('tenant.status');
            }

            abort(403, 'Organisasi ini belum aktif.');
        }

        return $next($request);
    }
}
