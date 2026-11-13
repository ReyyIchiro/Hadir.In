{{--
    Kolom kata sandi dengan tombol lihat/sembunyikan. Tombolnya bekerja tanpa
    JavaScript dan tetap bisa dioperasikan dengan papan ketik: ini checkbox
    asli yang disembunyikan secara visual, bukan div dengan onclick.
--}}
@props([
    'name',
    'label' => 'Kata sandi',
    'value' => null,
    'help' => null,
    'required' => false,
    'autocomplete' => 'current-password',
])

@php
    $id = $attributes->get('id') ?? $name;
    $messages = $errors->get($name);
    $error = $messages[0] ?? null;
@endphp

<div class="space-y-1.5" data-password-field>
    <x-field-label :for="$id" :value="$label" :required="$required" />

    <div class="password-field">
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="password"
            value="{{ old($name, $value) }}"
            @if($required) required @endif
            @if($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            autocomplete="{{ $autocomplete }}"
            {{ $attributes->merge(['class' => 'field-input password-field-input']) }}
        >

        <label class="password-field-toggle" data-password-toggle>
            <input type="checkbox" class="sr-only" data-password-checkbox aria-label="Tampilkan kata sandi">
            <span class="password-field-hit" aria-hidden="true"></span>
            <x-icon name="eye" :size="17" class="password-field-eye" />
            <x-icon name="eye-off" :size="17" class="password-field-eye-off" />
        </label>
    </div>

    @if($help)
        <p class="field-help">{{ $help }}</p>
    @endif

    @if($error)
        <ul id="{{ $id }}-error" class="field-error-list" role="alert">
            @foreach($messages as $message)
                <li class="field-error">
                    <x-icon name="alert-triangle" :size="14" class="mt-px shrink-0" />
                    <span>{{ $message }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
