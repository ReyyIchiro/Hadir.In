@props(['for' => null, 'value' => null, 'required' => false, 'help' => null])

<label @if($for) for="{{ $for }}" @endif {{ $attributes->merge(['class' => 'field-label']) }}>
    {{ $value ?? $slot }}@if($required)<span class="text-stamp" aria-hidden="true"> *</span>@endif
</label>
@if($help)
    <p class="field-help">{{ $help }}</p>
@endif
