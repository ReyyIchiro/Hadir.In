<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\SelfieService;
use App\Support\Flags;
use Illuminate\Console\Command;

class PurgeSelfies extends Command
{
    protected $signature = 'selfies:purge';

    protected $description = 'Hapus berkas selfie yang melewati masa retensi';

    public function handle(SelfieService $selfies): int
    {
        $purged = 0;

        Tenant::query()->chunkById(100, function ($tenants) use ($selfies, &$purged) {
            foreach ($tenants as $tenant) {
                $days = $tenant->settings?->selfie_retention_days ?? 90;

                \App\Models\AttendanceEvent::withoutGlobalScopes()
                    ->where('tenant_id', $tenant->id)
                    ->whereNotNull('selfie_path')
                    ->where('occurred_at', '<', now()->subDays($days))
                    ->chunkById(200, function ($events) use ($selfies, &$purged) {
                        foreach ($events as $event) {
                            $selfies->delete($event->selfie_path);
                            $event->forceFill(['selfie_path' => null])->save();
                            $purged++;
                        }
                    });
            }
        });

        $this->info("Selfie dihapus: {$purged}.");

        return self::SUCCESS;
    }
}
