{{--
    Satu-satunya sumber bootstrap tema. Dijalankan sebelum CSS dirender supaya
    tidak ada kedipan tema yang salah. Semua layout memakai partial ini.
--}}
<meta name="theme-color" content="#f5f2ea" data-theme-color>
<script{!! csp_script_nonce() !!}>
    (function () {
        var stored = localStorage.getItem('hadir-theme');
        var dark = stored === 'dark'
            || (stored !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);

        if (dark) {
            document.documentElement.dataset.theme = 'dark';
        }

        document.querySelector('[data-theme-color]')
            .setAttribute('content', dark ? '#111411' : '#f5f2ea');
    })();
</script>
