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

// Peta hanya dipakai di halaman presensi masuk, presensi pulang, dan form lokasi.
// Modul ini dimuat lewat dynamic import dari app.js supaya halaman lain tidak
// mengunduh 150 KB JavaScript dan CSS Leaflet yang tidak terpakai.
export default L;
