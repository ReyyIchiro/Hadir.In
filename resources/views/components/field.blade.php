@props(['name', 'label' => null, 'value' => null, 'help' => null, 'required' => false, 'type' => 'text'])

@php
    $id = $attributes->get('id') ?? $name;
    $error = $errors->first($name);
@endphp

<div class="space-y-1.5">
    @if($label)
        <x-field-label :for="$id" :value="$label" :required="$required" />
    @endif

    @if($type === 'textarea')
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            @if($required) required @endif
            @if($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->merge(['class' => 'field-input']) }}
        >{{ old($name, $value) }}</textarea>
    @else
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ old($name, $value) }}"
            @if($required) required @endif
            @if($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->merge(['class' => 'field-input']) }}
        >
    @endif

    @if($help)
        <p class="field-help">{{ $help }}</p>
    @endif

    @if($error)
        <p id="{{ $id }}-error" class="field-error" role="alert">
            <x-icon name="alert-triangle" :size="14" class="mt-px shrink-0" />
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
