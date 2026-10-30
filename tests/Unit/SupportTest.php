<?php

use App\Support\Flags;
use App\Support\Waktu;

it('menampilkan nama bulan dalam bahasa Indonesia', function () {
    expect(Waktu::monthLabel(1))->toBe('Januari')
        ->and(Waktu::monthLabel(8))->toBe('Agustus')
        ->and(Waktu::monthLabel(12))->toBe('Desember');
});

it('memberi label Indonesia untuk setiap tanda presensi', function () {
    expect(Flags::label('tanpa_lokasi'))->toBe('Tanpa lokasi')
        ->and(Flags::label('final_otomatis'))->toBe('Final otomatis')
        ->and(Flags::label('akurasi_rendah'))->toBe('Akurasi rendah');
});

it('memakai kode tanda apa adanya bila belum ada label', function () {
    expect(Flags::label('tanda_baru'))->toBe('tanda_baru');
});

it('menggabungkan tanda tanpa duplikat dan mempertahankan urutan', function () {
    $merged = Flags::merge(['tanpa_lokasi'], ['akurasi_rendah', 'tanpa_lokasi']);

    expect($merged)->toBe(['tanpa_lokasi', 'akurasi_rendah']);
});

it('mengembalikan daftar kosong saat tidak ada tanda', function () {
    expect(Flags::merge(null, []))->toBe([]);
});
