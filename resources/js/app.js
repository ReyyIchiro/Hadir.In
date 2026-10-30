import './bootstrap';

import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Vite hashes the image assets, so point Leaflet at the bundled URLs instead of
// the relative paths it guesses from the stylesheet.
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
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
