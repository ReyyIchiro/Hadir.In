<?php

namespace App\Console\Commands;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Services\FinalizationService;
use App\Support\TenantContext;
use Illuminate\Console\Command;

class FinalizeAttendance extends Command
{
    protected $signature = 'attendance:finalize {--tenant= : ID organisasi tertentu}';

    protected $description = 'Finalisasi hari presensi yang sudah melewati batas dan tandai absen';

    public function handle(FinalizationService $finalization): int
    {
        $query = Tenant::query()->where('status', TenantStatus::Active->value);

        if ($this->option('tenant')) {
            $query->whereKey($this->option('tenant'));
        }

        $total = ['finalized' => 0, 'absent' => 0];

        $query->chunkById(100, function ($tenants) use ($finalization, &$total) {
            $context = app(TenantContext::class);

            foreach ($tenants as $tenant) {
                // Job sistem berjalan tanpa sesi; set konteks per tenant agar
                // scope tenant tetap aktif di dalam service, lalu bersihkan.
                $context->set($tenant);

                try {
                    $counts = $finalization->runForTenant($tenant);
                    $total['finalized'] += $counts['finalized'];
                    $total['absent'] += $counts['absent'];
                } finally {
                    $context->clear();
                }
            }
        });

        $this->info(sprintf(
            'Finalisasi selesai: %d hari difinalisasi, %d hari ditandai absen.',
            $total['finalized'],
            $total['absent'],
        ));

        return self::SUCCESS;
    }
}
