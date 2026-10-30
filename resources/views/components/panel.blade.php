@props(['title' => null, 'description' => null, 'class' => ''])

<section {{ $attributes->merge(['class' => 'panel '.$class]) }}>
    @if($title || isset($actions))
        <header class="panel-head flex flex-wrap items-start justify-between gap-3">
            <div>
                @if($title)
                    <h2 class="font-display text-[17px] font-semibold leading-6">{{ $title }}</h2>
                @endif
                @if($description)
                    <p class="mt-0.5 text-[13px] leading-5 text-muted">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    <div class="{{ $title || isset($actions) ? '' : 'panel-pad' }}">
        {{ $slot }}
    </div>
</section>
