<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Presensi {{ $monthLabel }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 18mm 14mm 18mm 14mm;
        }

        * { box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            color: #111;
            margin: 0;
        }

        h1 { font-size: 16pt; margin: 0 0 2mm 0; }
        h2 { font-size: 11pt; margin: 0 0 2mm 0; border-bottom: 1px solid #111; padding-bottom: 1mm; }
        p { margin: 0 0 1.5mm 0; }

        .header { border-bottom: 2px solid #111; padding-bottom: 3mm; margin-bottom: 5mm; }
        .org { font-size: 12pt; font-weight: bold; }
        .muted { color: #444; }
        .meta { margin-top: 2mm; }
        .meta td { padding: 0.5mm 4mm 0.5mm 0; vertical-align: top; }

        .section { margin-bottom: 6mm; }

        .summary { width: 100%; border-collapse: collapse; }
        .summary th, .summary td { border: 1px solid #111; padding: 1.5mm 2mm; text-align: center; }
        .summary th { background: #eee; font-size: 8pt; }
        .summary td { font-size: 12pt; font-weight: bold; }

        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #111; padding: 1.2mm 1.5mm; vertical-align: top; }
        table.data th { background: #eee; font-size: 8pt; text-align: left; }
        table.data td { font-size: 8pt; }
        table.data thead { display: table-header-group; }
        table.data tr { page-break-inside: avoid; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }

        .rekap td, .rekap th { text-align: left; }
        .rekap .num { text-align: center; }

        .sign { width: 100%; margin-top: 6mm; border-collapse: collapse; }
        .sign td { width: 33%; padding: 0 4mm; vertical-align: top; text-align: center; }
        .sign .line { margin-top: 16mm; border-top: 1px solid #111; padding-top: 1mm; }

        .empty { padding: 4mm; border: 1px dashed #888; text-align: center; }
        .note { margin-bottom: 2mm; }
    </style>
</head>
<body>
    @if($section === 'rekap' && $first)
        <div class="header">
            <p class="org">{{ $tenant->name }}</p>
            <h1>Laporan Presensi Bulanan</h1>
            <p class="muted">Bukti kehadiran untuk stakeholder. Dibuat {{ \App\Support\Waktu::dateLong($generatedAt) }}.</p>
            <table class="meta">
                <tr>
                    <td class="muted">Periode</td>
                    <td><strong>{{ $monthLabel }}</strong></td>
                    <td class="muted">Divisi</td>
                    <td>{{ $divisionName ?? 'Semua divisi' }}</td>
                </tr>
                <tr>
                    <td class="muted">Disusun oleh</td>
                    <td>{{ $membership->user?->name }} ({{ $membership->role->value }})</td>
                    <td class="muted">Lokasi</td>
                    <td>{{ $locationName ?? 'Semua lokasi' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Ringkasan</h2>
            <table class="summary">
                <thead>
                    <tr>
                        <th>Hari terjadwal</th>
                        <th>Hadir</th>
                        <th>Terlambat</th>
                        <th>Izin, sakit, cuti</th>
                        <th>Absen</th>
                        <th>Belum difinalisasi</th>
                        <th>Perlu ditinjau</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $summary['scheduled'] }}</td>
                        <td>{{ $summary['present'] }}</td>
                        <td>{{ $summary['late'] }}</td>
                        <td>{{ $summary['leave'] }}</td>
                        <td>{{ $summary['absent'] }}</td>
                        <td>{{ $summary['not_finalized'] }}</td>
                        <td>{{ $summary['pending_review'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    @if($section === 'rekap')
        <div class="section">
            <h2>{{ $first ? 'Rekap per anggota' : 'Rekap per anggota lanjutan' }}</h2>
            @if($rowFrom > 1)
                <p class="muted note">Lanjutan, anggota {{ $rowFrom }} sampai {{ $rowTo }}.</p>
            @endif
            <table class="data rekap">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Divisi</th>
                        <th>Lokasi</th>
                        <th class="num">Hari terjadwal</th>
                        <th class="num">Hadir</th>
                        <th class="num">Terlambat</th>
                        <th class="num">Izin, sakit, cuti</th>
                        <th class="num">Absen</th>
                        <th class="num">Belum difinalisasi</th>
                        <th class="num">Perlu ditinjau</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $member)
                        <tr>
                            <td>{{ $member['name'] }}</td>
                            <td>{{ $member['division'] ?: '--' }}</td>
                            <td>{{ $member['location'] ?: '--' }}</td>
                            <td class="num">{{ $member['scheduled'] }}</td>
                            <td class="num">{{ $member['present'] }}</td>
                            <td class="num">{{ $member['late'] }}</td>
                            <td class="num">{{ $member['leave'] }}</td>
                            <td class="num">{{ $member['absent'] }}</td>
                            <td class="num">{{ $member['not_finalized'] }}</td>
                            <td class="num">{{ $member['pending_review'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="muted">Tidak ada data pada periode dan filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if($section === 'detail')
        <div class="section">
            <h2>{{ $first ? 'Rincian harian' : 'Rincian harian lanjutan' }}</h2>
            <p class="muted note">
                {{ $total }} baris tercetak. Menampilkan baris {{ $rowFrom }} sampai {{ $rowTo }}.
                Selfie dan koordinat tidak dicetak pada laporan ini.
            </p>
            <table class="data">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Nama</th>
                        <th>Jadwal</th>
                        <th>Masuk</th>
                        <th>Pulang</th>
                        <th>Status</th>
                        <th>Catatan</th>
                        <th>Tanda</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ \App\Support\Waktu::dateShort($row['date']) }}</td>
                            <td>{{ $row['day_name'] }}</td>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $row['schedule'] }}</td>
                            <td>{{ $row['check_in'] ? \App\Support\Waktu::time($row['check_in']) : '--:--' }}</td>
                            <td>{{ $row['check_out'] ? \App\Support\Waktu::time($row['check_out']) : '--:--' }}</td>
                            <td>{{ $row['status_label'] }}</td>
                            <td>{{ $row['note'] ? \Illuminate\Support\Str::limit($row['note'], 120) : '--' }}</td>
                            <td>{{ $row['flags'] ? \Illuminate\Support\Str::limit(implode(', ', array_map(fn (string $flag) => \App\Support\Flags::label($flag), $row['flags'])), 90) : '--' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="muted">Tidak ada data pada periode dan filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if($section === 'sign')
        <table class="sign">
            <tr>
                <td>
                    <p class="muted">Mengetahui,</p>
                    <div class="line"><strong>{{ $tenant->owner()?->user?->name ?? '..............' }}</strong><br>Pemilik</div>
                </td>
                <td>
                    <p class="muted">Disusun oleh,</p>
                    <div class="line"><strong>{{ $membership->user?->name }}</strong><br>{{ ucfirst($membership->role->value) }}</div>
                </td>
                <td>
                    <p class="muted">Diterima oleh,</p>
                    <div class="line"><strong>..............</strong><br>Pimpinan</div>
                </td>
            </tr>
        </table>
    @endif
</body>
</html>
