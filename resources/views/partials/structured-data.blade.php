<?php
// Data terstruktur schema.org untuk halaman publik. Dikirim sebagai JSON mentah
// supaya mesin pencari membaca satu objek utuh, bukan potongan teks.
$orgName = config('app.name', 'Hadir.In');
$orgUrl = url('/');
?>
<script type="application/ld+json"{!! csp_script_nonce() !!}>{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => $orgName,
    'url' => $orgUrl,
    'logo' => asset('brand/favicon-512.png'),
    'description' => $description ?? 'Presensi dengan waktu server, lokasi, dan selfie untuk kantor, toko, dan tim lapangan di Indonesia.',
    'areaServed' => 'ID',
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json"{!! csp_script_nonce() !!}>{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => $orgName,
    'url' => $orgUrl,
    'inLanguage' => 'id',
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
