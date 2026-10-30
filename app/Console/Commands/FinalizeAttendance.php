<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\FinalizationService;
use Illuminate\Console\Command;

class FinalizeAttendance extends Command
{
    protected $signature = 'attendance:finalize {--tenant= : ID organisasi tertentu}';

    protected $description = 'Finalisasi hari presensi yang sudah melewati batas dan tandai absen';

    public function handle(FinalizationService $finalization): int
    {
        $query = Tenant::query();

        if ($this->option('tenant')) {
            $query->whereKey($this->option('tenant'));
        }

        $total = ['finalized' => 0, 'absent' => 0];

        $query->chunkById(100, function ($tenants) use ($finalization, &$total) {
            foreach ($tenants as $tenant) {
                $counts = $finalization->runForTenant($tenant);
                $total['finalized'] += $counts['finalized'];
                $total['absent'] += $counts['absent'];
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
