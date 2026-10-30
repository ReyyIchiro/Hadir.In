import './bootstrap';

import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// DESIGN.md menetapkan pin stempel, bukan ikon pustaka. Marker digambar sebagai
// div dengan warna stempel supaya tidak bergantung pada aset gambar Leaflet.
L.Marker.prototype.options.icon = L.divIcon({
    className: '',
    html: '<span class="map-pin"></span>',
    iconSize: [18, 18],
    iconAnchor: [9, 9],
});

window.L = L;

function applyTheme(theme) {
    const root = document.documentElement;
    if (theme === 'dark' || theme === 'light') {
        root.dataset.theme = theme;
    } else {
        delete root.dataset.theme;
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            root.dataset.theme = 'dark';
        }
    }
}

window.setHadirTheme = function (theme) {
    if (theme === 'system') {
        localStorage.removeItem('hadir-theme');
    } else {
        localStorage.setItem('hadir-theme', theme);
    }
    applyTheme(theme);
    window.dispatchEvent(new CustomEvent('hadir-theme-changed', { detail: theme }));
};

applyTheme(localStorage.getItem('hadir-theme') || 'system');

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (!localStorage.getItem('hadir-theme')) {
        applyTheme('system');
    }
});

window.copyText = async function (text) {
    try {
        await navigator.clipboard.writeText(text);
        return true;
    } catch {
        return false;
    }
};
