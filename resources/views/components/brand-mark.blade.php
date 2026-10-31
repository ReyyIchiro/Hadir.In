@props(['class' => '', 'light' => false])

{{-- Mark merek: dua bilah dan satu figur. Ukurannya mengikuti font-size induk
     supaya sejajar dengan wordmark di semua layout.

     Varian gelap tidak dipilih lewat JavaScript: kedua berkas dirender dan CSS
     yang menukarnya saat tema berubah. `light` memaksa varian terang untuk
     panel yang memang selalu gelap, seperti panel masuk. --}}
@if ($light)
    <img
        src="{{ asset('brand/hadir-in-mark-light.png') }}"
        alt=""
        aria-hidden="true"
        width="24"
        height="22"
        {{ $attributes->merge(['class' => 'brand-mark '.$class]) }}
    >
@else
    <img
        src="{{ asset('brand/hadir-in-mark.png') }}"
        alt=""
        aria-hidden="true"
        width="24"
        height="22"
        {{ $attributes->merge(['class' => 'brand-mark brand-mark-on-light '.$class]) }}
    >
    <img
        src="{{ asset('brand/hadir-in-mark-light.png') }}"
        alt=""
        aria-hidden="true"
        width="24"
        height="22"
        class="brand-mark brand-mark-on-dark {{ $class }}"
    >
@endif
