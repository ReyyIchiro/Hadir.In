<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan untuk setiap respons HTML.
 *
 * CSP script-src menggabungkan nonce dengan 'unsafe-inline' dan 'strict-dynamic'.
 * Peramban modern yang mendukung strict-dynamic mengabaikan 'self' dan
 * 'unsafe-inline', sehingga hanya skrip dengan nonce (tema, Vite, Livewire)
 * dan skrip yang dimuat oleh skrip tepercaya yang berjalan. Blok tema inline
 * menerima nonce lewat csp_script_nonce() dan Vite/Livewire menerimanya lewat
 * konfigurasi csp_nonce. Style tetap 'unsafe-inline' karena Livewire/Alpine
 * menyisipkan atribut style. 'unsafe-eval' diizinkan karena ekspresi Alpine
 * (x-data, @click) dikompilasi dengan new Function di halaman presensi dan
 * form lokasi. Shell aplikasi tetap memakai JavaScript biasa.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Nonce per permintaan, dibagikan ke partial tema dan Vite. Vite
        // menyimpannya untuk tag skrip; Livewire membaca Vite::cspNonce()
        // pada @livewireScripts.
        $nonce = base64_encode(random_bytes(16));
        config(['csp_nonce' => $nonce]);
        Vite::useCspNonce($nonce);

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(self), geolocation=(self), microphone=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        $nonce = "'nonce-".config('csp_nonce', '')."'";

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src {$nonce} 'strict-dynamic' 'unsafe-inline' 'unsafe-eval' https:",
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
