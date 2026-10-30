@props(['title', 'description' => null])

<div class="flex flex-col items-start gap-2 py-10">
    <h3 class="font-display text-[16px] font-semibold">{{ $title }}</h3>
    @if($description)
        <p class="max-w-md text-[14px] leading-6 text-muted">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-2">{{ $action }}</div>
    @endisset
</div>
