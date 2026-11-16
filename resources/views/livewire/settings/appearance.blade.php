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
                choose(value) {
                    window.setHadirTheme(value);
                    this.theme = value;
                }
            }"
            @hadir-theme-changed.window="theme = window.getHadirTheme()"
        >
            <div class="grid gap-3 sm:grid-cols-3" role="radiogroup" aria-label="Pilihan tema">
                <template x-for="option in [
                    { value: 'system', label: 'Ikuti sistem', note: 'Mengikuti pengaturan perangkat.' },
                    { value: 'light', label: 'Terang', note: 'Latar kertas, teks gelap.' },
                    { value: 'dark', label: 'Gelap', note: 'Latar gelap, teks terang.' }
                ]" :key="option.value">
                    <div class="flex flex-col gap-1">
                        <input
                            type="radio"
                            name="hadir-theme"
                            :value="option.value"
                            x-model="theme"
                            @change="choose(option.value)"
                            class="sr-only peer"
                        />
                        <button
                            type="button"
                            @click.prevent="choose(option.value)"
                            class="peer-checked:border-stamp peer-checked:bg-stamp-tint/40 rounded-[4px] border p-4 text-left transition-colors peer-focus:ring-2 peer-focus:ring-focus peer-focus:ring-offset-1"
                            :class="theme === option.value ? 'border-stamp bg-stamp-tint/40' : 'border-divider bg-card hover:border-muted'"
                        >
                            <span class="block font-display text-[14px] font-semibold" x-text="option.label"></span>
                            <span class="mt-1 block text-[12px] text-muted" x-text="option.note"></span>
                        </button>
                    </div>
                </template>
            </div>
            <p class="mt-4 text-[12px] leading-5 text-muted">
                Tema terang adalah tampilan utama. Mode gelap tetap memakai tata letak dan tipografi yang sama.
            </p>
        </div>
    </x-panel>
</div>
