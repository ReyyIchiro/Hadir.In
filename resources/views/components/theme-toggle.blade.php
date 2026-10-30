{{--
    Tombol tema. Sengaja tanpa Alpine karena layout publik tidak memuat
    Livewire. Perilakunya ditangani satu listener di resources/js/app.js.
--}}
@props(['size' => 18])

<button
    type="button"
    class="theme-toggle"
    data-theme-toggle
    aria-label="Ganti tema terang atau gelap"
    title="Ganti tema"
    {{ $attributes }}
>
    <span class="theme-toggle-icon theme-toggle-sun" aria-hidden="true">
        <x-icon name="sun" :size="$size" />
    </span>
    <span class="theme-toggle-icon theme-toggle-moon" aria-hidden="true">
        <x-icon name="moon" :size="$size" />
    </span>
</button>
