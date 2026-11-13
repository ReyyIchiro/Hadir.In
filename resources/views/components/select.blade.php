@props(['name', 'label' => null, 'options' => [], 'value' => null, 'placeholder' => null, 'help' => null, 'required' => false])

@php
    $id = $attributes->get('id') ?? $name;
    $error = $errors->first($name);
    $selected = old($name, $value);
@endphp

<div class="space-y-1.5">
    @if($label)
        <x-field-label :for="$id" :value="$label" :required="$required" />
    @endif

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if($required) required @endif
        @if($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->merge(['class' => 'field-input select-field']) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>

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
