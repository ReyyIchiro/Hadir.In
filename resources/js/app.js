import './bootstrap';

// Leaflet hanya dibutuhkan di tiga layar: presensi masuk, presensi pulang, dan
// form lokasi. Memuatnya di sini membuat setiap halaman, termasuk halaman masuk
// dan halaman publik, mengunduh sekitar 150 KB JavaScript dan CSS peta.
// Halaman yang memakai peta menunggu hadirLoadMap() sebelum menggambar peta.
let mapPromise = null;

window.hadirLoadMap = function () {
    mapPromise ??= import('./map.js');

    return mapPromise;
};

// Tema punya tiga keadaan: 'system', 'light', dan 'dark'. Pilihan disimpan di
// localStorage; 'system' berarti tidak ada kunci sama sekali. Bootstrap awal
// ada di partials/theme.blade.php supaya tidak ada kedipan tema yang salah.
const themeMedia = window.matchMedia('(prefers-color-scheme: dark)');

function storedTheme() {
    const value = localStorage.getItem('hadir-theme');

    return value === 'dark' || value === 'light' ? value : 'system';
}

function resolvedTheme(theme = storedTheme()) {
    if (theme === 'system') {
        return themeMedia.matches ? 'dark' : 'light';
    }

    return theme;
}

function paintTheme(theme) {
    const resolved = resolvedTheme(theme);
    const root = document.documentElement;

    if (resolved === 'dark') {
        root.dataset.theme = 'dark';
    } else {
        delete root.dataset.theme;
    }

    const meta = document.querySelector('[data-theme-color]');
    if (meta) {
        meta.setAttribute('content', resolved === 'dark' ? '#111411' : '#f5f2ea');
    }

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', resolved === 'dark' ? 'true' : 'false');
    });

    window.dispatchEvent(new CustomEvent('hadir-theme-changed', {
        detail: { theme, resolved },
    }));
}

window.setHadirTheme = function (theme) {
    if (theme === 'system') {
        localStorage.removeItem('hadir-theme');
    } else if (theme === 'dark' || theme === 'light') {
        localStorage.setItem('hadir-theme', theme);
    } else {
        return;
    }

    paintTheme(theme);
};

window.getHadirTheme = storedTheme;

// Satu listener untuk semua tombol tema, termasuk yang ditambahkan kemudian
// oleh Livewire. Tombol tidak perlu Alpine.
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-theme-toggle]');
    if (!button) return;

    window.setHadirTheme(resolvedTheme() === 'dark' ? 'light' : 'dark');
});

themeMedia.addEventListener('change', () => {
    if (storedTheme() === 'system') {
        paintTheme('system');
    }
});

paintTheme();

window.copyText = async function (text) {
    try {
        await navigator.clipboard.writeText(text);
        return true;
    } catch {
        return false;
    }
};

// Tombol mata pada kolom kata sandi. State disimpan di checkbox asli supaya
// CSS bisa mengganti ikon tanpa JavaScript; di sini hanya tipe input yang
// diubah. Nilai sandi tidak pernah disalin ke mana pun.
function syncPasswordField(field) {
    const checkbox = field.querySelector('[data-password-checkbox]');
    const input = field.querySelector('input[type="password"], input[data-password-visible]');

    if (!checkbox || !input) return;

    if (checkbox.checked) {
        input.type = 'text';
        input.dataset.passwordVisible = 'true';
        input.setAttribute('aria-label', 'Kata sandi terlihat');
    } else {
        input.type = 'password';
        delete input.dataset.passwordVisible;
        input.setAttribute('aria-label', 'Kata sandi tersembunyi');
    }
}

document.addEventListener('change', (event) => {
    const checkbox = event.target.closest('[data-password-checkbox]');
    if (!checkbox) return;

    syncPasswordField(checkbox.closest('[data-password-field]'));
});

document.querySelectorAll('[data-password-field]').forEach(syncPasswordField);


// DESIGN.md meminta petunjuk geser pada tabel lebar di layar kecil. Petunjuk
// hanya muncul bila tabel benar-benar melebar, bukan pada setiap tabel.
const tableHints = new WeakMap();

function syncTableHints() {
    document.querySelectorAll('.table-scroll').forEach((scroller) => {
        let entry = tableHints.get(scroller);

        if (!entry || !entry.hint.isConnected) {
            entry?.observer.disconnect();

            const hint = document.createElement('p');
            hint.className = 'table-scroll-hint';
            hint.textContent = 'Geser untuk melihat kolom lain.';
            scroller.before(hint);

            const update = () => {
                hint.classList.toggle('is-visible', scroller.scrollWidth > scroller.clientWidth + 1);
            };

            const observer = new ResizeObserver(update);
            observer.observe(scroller);

            entry = { hint, observer, update };
            tableHints.set(scroller, entry);
        }

        entry.update();
    });
}

let tableHintFrame = null;
new MutationObserver(() => {
    if (tableHintFrame !== null) return;

    tableHintFrame = requestAnimationFrame(() => {
        tableHintFrame = null;
        syncTableHints();
    });
}).observe(document.body, { childList: true, subtree: true });

syncTableHints();

// ---------------------------------------------------------------------------
// Konfirmasi tindakan berisiko
// ---------------------------------------------------------------------------
//
// Dua langkah: pengguna menekan tombol aksi, dialog menjelaskan akibatnya,
// lalu pengguna mengonfirmasi. Tidak ada aksi berisiko yang berjalan hanya
// karena satu klik. Dialog ini menggantikan confirm() bawaan peramban.

const confirmState = {
    trigger: null,
    pending: null,
    resolve: null,
};

function confirmDialog() {
    return document.querySelector('[data-confirm-dialog]');
}

function closeConfirm(result) {
    const dialog = confirmDialog();
    if (!dialog) return;

    dialog.hidden = true;
    document.body.style.removeProperty('overflow');

    const resolve = confirmState.resolve;
    const trigger = confirmState.trigger;

    confirmState.pending = null;
    confirmState.resolve = null;
    confirmState.trigger = null;

    if (trigger && trigger.isConnected) trigger.focus();
    if (resolve) resolve(result);
}

function openConfirm(trigger) {
    const dialog = confirmDialog();
    if (!dialog) return Promise.resolve(true);

    const title = trigger.dataset.confirm || 'Lanjutkan tindakan ini?';
    const detail = trigger.dataset.confirmDetail || '';
    const action = trigger.dataset.confirmAction || 'Lanjutkan';

    dialog.querySelector('[data-confirm-title]').textContent = title;
    dialog.querySelector('[data-confirm-detail-text]').textContent = detail;

    const accept = dialog.querySelector('[data-confirm-accept]');
    accept.textContent = action;

    dialog.hidden = false;
    document.body.style.overflow = 'hidden';

    confirmState.trigger = trigger;

    // Fokus awal ke Batal: Enter yang tidak sengaja tidak menjalankan aksi.
    dialog.querySelector('[data-confirm-cancel]:not(.confirm-dialog__backdrop)').focus();

    return new Promise((resolve) => {
        confirmState.resolve = resolve;
    });
}

function isDestructiveTrigger(element) {
    return element.matches('[data-confirm]');
}

document.addEventListener('click', async (event) => {
    const cancel = event.target.closest('[data-confirm-cancel]');
    if (cancel) {
        event.preventDefault();
        closeConfirm(false);
        return;
    }

    const accept = event.target.closest('[data-confirm-accept]');
    if (accept) {
        event.preventDefault();
        const pending = confirmState.pending;
        closeConfirm(true);

        if (pending) pending();

        return;
    }

    const trigger = event.target.closest('[data-confirm]');
    if (!trigger) return;

    // Tombol yang sudah dikonfirmasi: biarkan aksi aslinya berjalan.
    if (trigger.dataset.confirmConfirmed === 'true') {
        delete trigger.dataset.confirmConfirmed;
        return;
    }

    const form = trigger.closest('form');
    const isLivewireAction = trigger.hasAttribute('wire:click');

    // Semua jalur berhenti dulu: tidak ada aksi berisiko tanpa langkah kedua.
    event.preventDefault();
    event.stopImmediatePropagation();

    const ok = await openConfirm(trigger);
    if (!ok) return;

    trigger.dataset.confirmConfirmed = 'true';

    if (isLivewireAction) {
        trigger.click();

        return;
    }

    // Form biasa maupun form Livewire: requestSubmit mempertahankan submitter
    // dan tetap menjalankan validasi serta listener Livewire.
    if (form) {
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit(trigger);
        } else {
            form.submit();
        }
    }
}, true);

document.addEventListener('keydown', (event) => {
    const dialog = confirmDialog();
    if (!dialog || dialog.hidden) return;

    if (event.key === 'Escape') {
        event.preventDefault();
        closeConfirm(false);
        return;
    }

    // Jebak fokus di dalam dialog selama terbuka.
    if (event.key === 'Tab') {
        const focusable = [...dialog.querySelectorAll('button, [href], input, select, textarea')]
            .filter((element) => !element.disabled && element.offsetParent !== null);

        if (focusable.length === 0) return;

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }
});
