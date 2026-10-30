@props(['name', 'size' => 20, 'class' => ''])

@php
    $paths = [
        'clock' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 1.8"/>',
        'camera' => '<path d="M4 8.5h3l1.4-2h7.2L17 8.5h3v9H4z"/><circle cx="12" cy="13" r="3.2"/>',
        'calendar' => '<rect x="4" y="5.5" width="16" height="14" rx="1.5"/><path d="M8 3.5v4M16 3.5v4M4 10h16"/>',
        'user' => '<circle cx="12" cy="8.5" r="3.5"/><path d="M5 20c.5-3.6 3-5.5 7-5.5s6.5 1.9 7 5.5"/>',
        'users' => '<circle cx="9" cy="8.5" r="3"/><path d="M3.5 19c.4-3.2 2.4-5 5.5-5s5.1 1.8 5.5 5"/><path d="M16 5.8a3 3 0 0 1 0 5.4M17.5 14.4c1.8.5 2.8 2 3 4.6"/>',
        'map-pin' => '<path d="M12 21s6.5-6 6.5-11a6.5 6.5 0 0 0-13 0C5.5 15 12 21 12 21z"/><circle cx="12" cy="10" r="2.2"/>',
        'alert-triangle' => '<path d="M12 4 2.8 20h18.4z"/><path d="M12 9.5v4.2M12 17h.01"/>',
        'check' => '<path d="m5 12.5 4.5 4.5L19 7"/>',
        'x' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'arrow-right' => '<path d="M4 12h15M13 6l6 6-6 6"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2.5v2M12 19.5v2M2.5 12h2M19.5 12h2M5.2 5.2l1.4 1.4M17.4 17.4l1.4 1.4M18.8 5.2l-1.4 1.4M6.6 17.4l-1.4 1.4"/>',
        'moon' => '<path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5z"/>',
        'home' => '<path d="m4 10 8-6 8 6v9H4z"/><path d="M9.5 19v-5h5v5"/>',
        'clipboard' => '<rect x="5" y="4" width="14" height="17" rx="1.5"/><path d="M9 4.5V3h6v1.5M8.5 9h7M8.5 13h7M8.5 17h4"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 3v2.2M12 18.8V21M3 12h2.2M18.8 12H21M5.6 5.6l1.6 1.6M16.8 16.8l1.6 1.6M18.4 5.6l-1.6 1.6M7.2 16.8l-1.6 1.6"/>',
        'logout' => '<path d="M10 5H5v14h5M14 8l4 4-4 4M8 12h10"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-left' => '<path d="m14 6-6 6 6 6"/>',
        'chevron-right' => '<path d="m10 6 6 6-6 6"/>',
        'download' => '<path d="M12 4v10M8 10l4 4 4-4M5 19h14"/>',
        'building' => '<path d="M4 21V5.5L12 3v18M12 9h8v12M8 8h.01M8 12h.01M8 16h.01M16 13h.01M16 17h.01"/>',
    ];

    $path = $paths[$name] ?? '';
@endphp

<svg
    xmlns="http://www.w3.org/2000/svg"
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.5"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    focusable="false"
    {{ $attributes->merge(['class' => $class]) }}
>
    {!! $path !!}
</svg>
