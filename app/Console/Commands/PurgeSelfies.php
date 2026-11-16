<?php

namespace App\Console\Commands;

use App\Models\AttendanceEvent;
use App\Models\Tenant;
use App\Services\SelfieService;
use App\Support\TenantContext;
use Illuminate\Console\Command;

class PurgeSelfies extends Command
{
    protected $signature = 'selfies:purge';

    protected $description = 'Hapus berkas selfie yang melewati masa retensi';

    public function handle(SelfieService $selfies): int
    {
        $purged = 0;

        Tenant::query()->chunkById(100, function ($tenants) use ($selfies, &$purged) {
            $context = app(TenantContext::class);

            foreach ($tenants as $tenant) {
                // Job sistem berjalan tanpa sesi; set konteks per tenant agar
                // relasi settings dan event ter-scope tenant ini saja.
                $context->set($tenant);

                try {
                    $purged += $this->purgeTenant($tenant, $selfies);
                } finally {
                    $context->clear();
                }
            }
        });

        $this->info("Selfie dihapus: {$purged}.");

        return self::SUCCESS;
    }

    protected function purgeTenant(Tenant $tenant, SelfieService $selfies): int
    {
        $purged = 0;
        $days = $tenant->settings?->selfie_retention_days ?? 90;

        AttendanceEvent::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereNotNull('selfie_path')
            ->where('occurred_at', '<', now()->subDays($days))
            ->chunkById(200, function ($events) use ($selfies, &$purged) {
                foreach ($events as $event) {
                    // Path dikosongkan hanya bila berkas benar-benar
                    // hilang; kegagalan disk dibiarkan untuk percobaan
                    // berikutnya agar tidak ada berkas yatim.
                    if (! $selfies->delete($event->selfie_path)) {
                        continue;
                    }

                    $event->forceFill(['selfie_path' => null])->save();
                    $purged++;
                }
            });

        return $purged;
    }
}
