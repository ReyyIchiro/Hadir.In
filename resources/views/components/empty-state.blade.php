@props(['title', 'description' => null])

<div class="flex flex-col items-start gap-2 px-4 py-8 md:px-6">
    <span class="folio" aria-hidden="true">00</span>
    <h3 class="text-[15px] font-medium">{{ $title }}</h3>
    @if($description)
        <p class="max-w-md text-[13px] leading-6 text-muted">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-2">{{ $action }}</div>
    @endisset
</div>
