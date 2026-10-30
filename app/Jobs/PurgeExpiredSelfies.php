<?php

namespace App\Jobs;

use App\Models\AttendanceEvent;
use App\Services\SelfieService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PurgeExpiredSelfies implements ShouldQueue
{
    use Queueable;

    public function handle(SelfieService $selfies): void
    {
        AttendanceEvent::withoutGlobalScopes()
            ->whereNotNull('selfie_path')
            ->where('occurred_at', '<', now()->subDays(90))
            ->chunkById(200, function ($events) use ($selfies) {
                foreach ($events as $event) {
                    $selfies->delete($event->selfie_path);
                    $event->forceFill(['selfie_path' => null])->save();
                }
            });
    }
}
