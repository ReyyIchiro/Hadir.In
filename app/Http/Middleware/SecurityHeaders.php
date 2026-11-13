<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan untuk setiap respons HTML.
 *
 * CSP masih mengizinkan 'unsafe-inline' pada script dan style karena tema
 * dijalankan sebagai skrip inline sebelum CSS dimuat (partials/theme.blade.php)
 * dan Livewire/Alpine menyisipkan atribut style. Selama itu belum diganti nonce,
 * CSP di sini berfungsi menutup frame, plugin, dan sumber daya asing, bukan
 * menggantikan perlindungan XSS yang sudah ada di lapisan Blade dan policy.
 * 'unsafe-eval' sengaja tidak diizinkan: Livewire 3 tidak membutuhkannya.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(self), geolocation=(self), microphone=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob: https://tile.openstreetmap.org https://*.tile.openstreetmap.org",
            "font-src 'self' data:",
            "connect-src 'self' https://tile.openstreetmap.org https://*.tile.openstreetmap.org",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
        ]));

        // HSTS hanya dikirim lewat HTTPS; mengirimnya pada HTTP lokal akan
        // memaksa peramban developer memakai HTTPS untuk localhost.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
