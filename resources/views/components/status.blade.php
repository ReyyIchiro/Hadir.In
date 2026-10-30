@props(['status' => 'hadir', 'label' => null])

@php
    $labels = [
        'hadir' => 'Hadir',
        'terlambat' => 'Terlambat',
        'izin' => 'Izin',
        'sakit' => 'Sakit',
        'cuti' => 'Cuti',
        'libur' => 'Libur',
        'absen' => 'Absen',
        'belum_absen' => 'Belum absen',
        'terjadwal' => 'Terjadwal',
        'belum_pulang' => 'Belum pulang',
        'pending' => 'Menunggu',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
        'active' => 'Aktif',
        'suspended' => 'Ditangguhkan',
    ];

    $status = $status instanceof \BackedEnum ? $status->value : $status;
    $text = $label ?? ($labels[$status] ?? $status);
@endphp

<span {{ $attributes->merge(['class' => 'status-pill status-'.$status]) }}>
    {{ $text }}
</span>
