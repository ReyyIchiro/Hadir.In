<div>
    <h1 class="page-title">Tampilan</h1>
    <p class="mt-1 max-w-2xl text-[14px] leading-6 text-muted">
        Tema disimpan di peramban ini. Bila belum memilih, tampilan mengikuti pengaturan perangkat.
    </p>

    <x-panel class="mt-5" title="Tema" description="Pilih tampilan yang paling nyaman untuk mata.">
        <div
            class="panel-pad"
            x-data="{
                theme: localStorage.getItem('hadir-theme') || 'system',
                apply(value) {
                    this.theme = value;
                    if (value === 'system') {
                        localStorage.removeItem('hadir-theme');
                        const dark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                        document.documentElement.dataset.theme = dark ? 'dark' : 'light';
                    } else {
                        localStorage.setItem('hadir-theme', value);
                        document.documentElement.dataset.theme = value;
                    }
                }
            }"
        >
            <div class="grid gap-3 sm:grid-cols-3" role="radiogroup" aria-label="Pilihan tema">
                <template x-for="option in [
                    { value: 'system', label: 'Ikuti sistem', note: 'Mengikuti pengaturan perangkat.' },
                    { value: 'light', label: 'Terang', note: 'Latar kertas, teks gelap.' },
                    { value: 'dark', label: 'Gelap', note: 'Latar gelap, teks terang.' }
                ]" :key="option.value">
                    <button
                        type="button"
                        @click="apply(option.value)"
                        role="radio"
                        :aria-checked="theme === option.value ? 'true' : 'false'"
                        class="rounded-[4px] border p-4 text-left transition-colors"
                        :class="theme === option.value ? 'border-stamp bg-stamp-tint/40' : 'border-divider bg-card hover:border-muted'"
                    >
                        <span class="block font-display text-[14px] font-semibold" x-text="option.label"></span>
                        <span class="mt-1 block text-[12px] text-muted" x-text="option.note"></span>
                    </button>
                </template>
            </div>
            <p class="mt-4 text-[12px] leading-5 text-muted">
                Tema terang adalah tampilan utama. Mode gelap tetap memakai tata letak dan tipografi yang sama.
            </p>
        </div>
    </x-panel>
</div>
