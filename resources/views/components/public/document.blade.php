@props([
    'sections',
    'folio' => null,
])

<div class="container-page">
    @if ($folio)
        <p class="folio hidden pt-10 md:pt-12 lg:block">{{ $folio }}</p>
    @endif

    <div class="grid gap-6 py-8 md:py-10 lg:grid-cols-12 lg:gap-8">
        <x-public.toc :items="collect($sections)->map(fn ($section) => ['id' => $section['id'], 'label' => $section['title']])->all()" />

        <div class="lg:col-span-8 lg:col-start-5">
            <div class="space-y-10">
                @foreach ($sections as $index => $section)
                    <section id="{{ $section['id'] }}" class="scroll-mt-24 border-t border-divider pt-6">
                        <div class="flex items-baseline gap-3">
                            <span class="folio text-stamp">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <h2 class="font-display text-[24px] font-medium leading-[1.2] tracking-[-0.02em]">{{ $section['title'] }}</h2>
                        </div>
                        <div class="mt-3 space-y-3 text-[14px] leading-7 text-secondary">
                            @foreach ($section['paragraphs'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
</div>
