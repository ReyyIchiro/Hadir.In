<?php

if (! function_exists('csp_script_nonce')) {
    /**
     * Nonce CSP untuk tag <script> inline pada permintaan berjalan.
     * Dibuat oleh App\Http\Middleware\SecurityHeaders.
     */
    function csp_script_nonce(): string
    {
        $nonce = (string) config('csp_nonce', '');

        return $nonce === '' ? '' : ' nonce="'.$nonce.'"';
    }
}
