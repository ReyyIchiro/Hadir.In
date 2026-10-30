<?php

namespace App\Providers;

use App\Http\Middleware\EnsureTenantActive;
use App\Http\Middleware\ResolveTenant;
use App\Support\TenantContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    public function boot(): void
    {
        // Livewire updates post to a single endpoint, so tenant resolution has to
        // run again for every component request. Without this the tenant context
        // is empty and every interactive screen aborts with 403.
        Livewire::addPersistentMiddleware([
            ResolveTenant::class,
            EnsureTenantActive::class,
        ]);

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by(mb_strtolower((string) $request->input('email')).'|'.$request->ip());
        });
    }
}
