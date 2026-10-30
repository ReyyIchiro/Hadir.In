@props([
    'items',
    'label' => 'Isi',
])

<aside {{ $attributes->merge(['class' => 'lg:col-span-3']) }}>
    {{-- Di ponsel, indeks dilipat supaya isi dokumen tidak terdorong jauh ke bawah. --}}
    <details class="group border-y border-divider py-3 lg:hidden">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-[13px] font-medium marker:hidden">
            <span>Isi halaman</span>
            <span class="flex items-center gap-2">
                <span class="folio">{{ count($items) }} bagian</span>
                <span class="font-mono text-[15px] leading-none text-muted transition-transform group-open:rotate-45" aria-hidden="true">+</span>
            </span>
        </summary>
        <ol class="mt-3 space-y-2 pb-1">
            @foreach ($items as $index => $item)
                <li>
                    <a href="#{{ $item['id'] }}" class="flex gap-2 text-[13px] leading-5 text-muted transition-colors hover:text-text">
                        <span class="font-mono text-[11px] tabular-nums">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ol>
    </details>

    <nav aria-label="Isi halaman" class="hidden lg:sticky lg:top-8 lg:block">
        <p class="label">{{ $label }}</p>
        <ol class="mt-3 space-y-2">
            @foreach ($items as $index => $item)
                <li>
                    <a href="#{{ $item['id'] }}" class="flex gap-2 text-[13px] leading-5 text-muted transition-colors hover:text-text">
                        <span class="font-mono text-[11px] tabular-nums">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ol>
    </nav>
</aside>
