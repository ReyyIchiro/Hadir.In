<?php

use App\Http\Middleware\EnsurePlatformAdmin;
use App\Http\Middleware\EnsureTenantActive;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Diterapkan pada semua respons web, termasuk halaman galat, supaya
        // satu rute baru tidak pernah lolos tanpa header keamanan.
        $middleware->append(SecurityHeaders::class);

        // Produksi selalu berada di belakang Nginx (OPERATIONS.md bagian 5).
        // Tanpa ini, $request->secure() membaca koneksi Nginx ke PHP-FPM yang
        // selalu HTTP dan HSTS tidak pernah terkirim.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'tenant' => ResolveTenant::class,
            'tenant.active' => EnsureTenantActive::class,
            'platform' => EnsurePlatformAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
