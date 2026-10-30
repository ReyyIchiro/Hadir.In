@props([
    'folio',
    'title',
])

<section class="ink-band">
    <div class="container-page py-12 md:py-16">
        <div class="grid gap-8 lg:grid-cols-12 lg:gap-8">
            <div class="lg:col-span-4">
                <p class="folio">{{ $folio }}</p>
                <h2 class="display-md mt-4 max-w-[14ch]">{{ $title }}</h2>
            </div>
            <div class="lg:col-span-8">
                {{ $slot }}
            </div>
        </div>
    </div>
</section>
