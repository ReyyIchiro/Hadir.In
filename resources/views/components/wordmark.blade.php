@props(['href' => null, 'light' => false, 'label' => null])

@php($tag = $href ? 'a' : 'span')

{{-- Satu-satunya lockup merek. Mark duduk di garis dasar teks, bukan di
     tengah kotak baris, supaya kata tidak terlihat melayang di samping mark. --}}
<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'wordmark']) }}
>
    <x-brand-mark :light="$light" />
    <span class="wordmark-text">Hadir.In</span>
    @if ($label)
        <span class="wordmark-label">{{ $label }}</span>
    @endif
</{{ $tag }}>
