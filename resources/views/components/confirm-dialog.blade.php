{{--
    Dialog konfirmasi untuk tindakan berisiko. Menggantikan confirm() bawaan
    peramban karena dialog itu tidak bisa menjelaskan akibat, tidak konsisten
    antarsistem operasi, dan tidak punya langkah kedua yang eksplisit.

    Cara pakai pada tombol Livewire:
        data-confirm="Hapus hari libur ini?"
        data-confirm-detail="Hari libur hilang dari kalender dan laporan."
        data-confirm-action="Hapus"

    Untuk form biasa, tambahkan atribut yang sama pada <form>; dialog akan
    mengirim form setelah pengguna menekan tombol aksi.
--}}
<div
    class="confirm-dialog"
    data-confirm-dialog
    role="dialog"
    aria-modal="true"
    aria-labelledby="confirm-title"
    aria-describedby="confirm-detail"
    hidden
>
    <div class="confirm-dialog__backdrop" data-confirm-cancel></div>

    <div class="confirm-dialog__panel" role="document">
        <p class="folio" data-confirm-eyebrow>Konfirmasi</p>
        <h2 id="confirm-title" class="confirm-dialog__title" data-confirm-title></h2>
        <p id="confirm-detail" class="confirm-dialog__detail" data-confirm-detail-text></p>

        <div class="confirm-dialog__actions">
            <button type="button" class="btn btn-ghost" data-confirm-cancel>Batal</button>
            <button type="button" class="btn btn-danger" data-confirm-accept>Lanjutkan</button>
        </div>
    </div>
</div>
