@props([
    'folio',
    'label',
    'title',
    'lede' => null,
])

<section class="border-b border-divider">
    <div class="container-page">
        <div class="grid gap-10 py-10 md:py-14 lg:grid-cols-12 lg:gap-8 lg:py-20">
            <div class="lg:col-span-8">
                <div class="flex items-center gap-3">
                    <span class="folio">{{ $folio }}</span>
                    <span class="h-px w-8 bg-rule-strong" aria-hidden="true"></span>
                    <p class="label">{{ $label }}</p>
                </div>

                <h1 class="display-lg mt-6 max-w-[20ch]">{{ $title }}</h1>

                @if ($lede)
                    <p class="lede mt-5">{{ $lede }}</p>
                @endif

                @isset($actions)
                    <div class="mt-8 flex flex-col items-stretch gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                        {{ $actions }}
                    </div>
                @endisset
            </div>

            @isset($aside)
                <div class="lg:col-span-4 lg:pt-1">
                    <div class="border-t border-rule-strong pt-4">
                        {{ $aside }}
                    </div>
                </div>
            @endisset
        </div>
    </div>
</section>
