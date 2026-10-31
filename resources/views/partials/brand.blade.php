{{--
    Ikon dan metadata merek. Satu sumber untuk semua layout supaya favicon,
    warna bilah peramban, dan kartu pratinjau tidak pernah berbeda antar area.
--}}
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('brand/favicon-192.png') }}">
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('brand/favicon-512.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('brand/apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('brand/site.webmanifest') }}">
<meta name="application-name" content="Hadir.In">
<meta name="apple-mobile-web-app-title" content="Hadir.In">

<meta property="og:site_name" content="Hadir.In">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $title ?? 'Hadir.In' }}">
<meta property="og:description" content="{{ $description ?? 'Presensi dengan bukti waktu, tempat, dan wajah.' }}">
<meta property="og:image" content="{{ asset('brand/og-default.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">

{{-- URL kanonis dan og:url memakai URL saat ini tanpa query string supaya
     parameter pelacakan tidak membuat mesin pencari melihat halaman ganda.
     Hanya halaman publik yang memakainya; area aplikasi memasang noindex. --}}
@if ($canonical ?? false)
    <link rel="canonical" href="{{ $canonical }}">
    <meta property="og:url" content="{{ $canonical }}">
@endif
