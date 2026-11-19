<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Location;
use App\Models\Tenant;
use App\Services\ReportService;
use App\Support\Flags;
use App\Support\LaporanPdf;
use App\Support\TenantContext;
use App\Support\Waktu;
use Carbon\CarbonImmutable;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function csv(Request $request, ReportService $reports, TenantContext $context): StreamedResponse
    {
        $membership = $context->membership();
        abort_if($membership === null, 403);

        Gate::authorize('view-reports', $membership);

        $tenant = $context->tenant();
        abort_if($tenant === null, 403);

        $validated = $this->resolveFilters($request, $tenant);

        $divisionId = $validated['division_id'];
        $locationId = $validated['location_id'];

        $filename = 'laporan-presensi-'.$validated['month'].'.csv';

        return response()->streamDownload(function () use ($reports, $tenant, $validated, $divisionId, $locationId, $membership) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM so Excel on Windows reads Indonesian characters correctly.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Tanggal',
                'Hari',
                'Nama',
                'Email',
                'Divisi',
                'Lokasi',
                'Jadwal',
                'Masuk',
                'Pulang',
                'Status',
                'Catatan',
                'Tanda',
            ], ';', '"', '');

            // Baris mengalir dari generator per baris; bulan panjang tidak
            // pernah dihidrasi penuh ke memori (ARCHITECTURE 14).
            foreach ($reports->monthRows($tenant, $validated['month'], $divisionId, $locationId, $membership) as $row) {
                fputcsv($handle, [
                    $row['date'],
                    $row['day_name'],
                    self::safeCell($row['name']),
                    self::safeCell($row['email']),
                    self::safeCell($row['division']),
                    self::safeCell($row['location']),
                    $row['schedule'],
                    $row['check_in'] ? Waktu::time($row['check_in']) : '',
                    $row['check_out'] ? Waktu::time($row['check_out']) : '',
                    $row['status_label'],
                    self::safeCell((string) ($row['note'] ?? '')),
                    implode(', ', array_map(fn (string $flag) => Flags::label($flag), $row['flags'])),
                ], ';', '"', '');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    /**
     * Validasi bersama ekspor CSV dan PDF: format bulan, rentang 24 bulan
     * terakhir, dan keberadaan divisi atau lokasi yang dipilih.
     *
     * @return array{month: string, division_id: int|null, location_id: int|null}
     */
    protected function resolveFilters(Request $request, Tenant $tenant): array
    {
        $validated = $request->validate([
            'month' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'division_id' => ['nullable', 'integer'],
            'location_id' => ['nullable', 'integer'],
        ], [
            'month.required' => 'Bulan wajib diisi.',
            'month.regex' => 'Format bulan harus YYYY-MM.',
        ]);

        $start = CarbonImmutable::createFromFormat('Y-m', $validated['month'], $tenant->timezone)->startOfMonth();
        $earliest = Waktu::now($tenant)->startOfMonth()->subMonths(23);

        abort_if($start->lt($earliest), 422, 'Bulan di luar rentang 24 bulan terakhir.');
        abort_if($start->gt(Waktu::now($tenant)->startOfMonth()), 422, 'Bulan tidak boleh di masa depan.');

        if (! empty($validated['division_id'])) {
            abort_unless(Division::whereKey($validated['division_id'])->exists(), 422);
        }

        if (! empty($validated['location_id'])) {
            abort_unless(Location::whereKey($validated['location_id'])->exists(), 422);
        }

        return [
            'month' => $validated['month'],
            'division_id' => ! empty($validated['division_id']) ? (int) $validated['division_id'] : null,
            'location_id' => ! empty($validated['location_id']) ? (int) $validated['location_id'] : null,
        ];
    }

    public function pdf(Request $request, ReportService $reports, TenantContext $context)
    {
        $membership = $context->membership();
        abort_if($membership === null, 403);

        Gate::authorize('view-reports', $membership);

        $tenant = $context->tenant();
        abort_if($tenant === null, 403);

        $validated = $this->resolveFilters($request, $tenant);

        $rows = [];

        // Batas baris per berkas supaya PDF tidak membengkak tak terbatas.
        foreach ($reports->monthRows($tenant, $validated['month'], $validated['division_id'], $validated['location_id'], $membership) as $row) {
            $rows[] = $row;

            if (count($rows) >= 3000) {
                break;
            }
        }

        $summary = $this->summarize($rows);
        $rekap = $this->rekap($rows);

        $start = CarbonImmutable::createFromFormat('Y-m', $validated['month'], $tenant->timezone)->startOfMonth();
        $monthLabel = Waktu::monthLabel($start->month).' '.$start->year;

        $divisionName = $validated['division_id'] !== null
            ? optional(Division::find($validated['division_id']))->name
            : null;
        $locationName = $validated['location_id'] !== null
            ? optional(Location::find($validated['location_id']))->name
            : null;

        $base = [
            'tenant' => $tenant,
            'membership' => $membership,
            'summary' => $summary,
            'total' => count($rows),
            'monthLabel' => $monthLabel,
            'month' => $validated['month'],
            'divisionName' => $divisionName,
            'locationName' => $locationName,
            'generatedAt' => Waktu::now($tenant),
        ];

        // dompdf memakai memori sebanding jumlah sel. Satu bulan penuh bisa
        // melewati batas memori PHP, jadi dokumen dirender per bagian dan
        // per gugus 300 baris, lalu disatukan. Bagian pembuka memuat judul,
        // identitas, ringkasan, dan rekap; bagian detail memuat rincian
        // harian dan kolom tanda tangan.
        $parts = [];

        ini_set('memory_limit', '256M');

        $rekapChunks = array_chunk($rekap, 100);
        $lastRekapIndex = max(0, count($rekapChunks) - 1);

        if ($rekapChunks === []) {
            $rekapChunks = [[]];
        }

        foreach ($rekapChunks as $index => $chunk) {
            $parts[] = $this->renderPdfPart($base + [
                'section' => 'rekap',
                'rows' => $chunk,
                'rowFrom' => $index * 100 + 1,
                'rowTo' => $index * 100 + count($chunk),
                'first' => $index === 0,
                'last' => $index === $lastRekapIndex,
            ]);
        }

        $detailChunks = array_chunk($rows, 200);

        if ($detailChunks === []) {
            $detailChunks = [[]];
        }

        foreach ($detailChunks as $index => $chunk) {
            $parts[] = $this->renderPdfPart($base + [
                'section' => 'detail',
                'rows' => $chunk,
                'rowFrom' => $index * 200 + 1,
                'rowTo' => $index * 200 + count($chunk),
                'first' => $index === 0,
            ]);
        }

        // Tanda tangan berdiri sendiri supaya tidak terpotong di halaman
        // penuh saat tabel detail mengisi seluruh halaman terakhirnya.
        $parts[] = $this->renderPdfPart($base + ['section' => 'sign', 'rows' => []]);

        return response($this->mergePdfParts($parts, $monthLabel), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="laporan-presensi-'.$validated['month'].'.pdf"',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, int>
     */
    protected function summarize(array $rows): array
    {
        // Ringkasan dihitung dari baris yang benar-benar tercetak supaya
        // angka di halaman pertama tidak pernah bertentangan dengan isinya.
        $summary = [
            'scheduled' => count($rows),
            'present' => 0,
            'late' => 0,
            'leave' => 0,
            'absent' => 0,
            'not_finalized' => 0,
            'pending_review' => 0,
        ];

        foreach ($rows as $row) {
            if ($row['status'] === 'terlambat') {
                $summary['late']++;
                $summary['present']++;
            } elseif ($row['status'] === 'hadir') {
                $summary['present']++;
            } elseif (in_array($row['status'], ['izin', 'sakit', 'cuti'], true)) {
                $summary['leave']++;
            } elseif ($row['status'] === 'belum_absen') {
                $summary['not_finalized']++;
            } else {
                $summary['absent']++;
            }

            if (in_array('perlu_ditinjau', $row['flags'], true)) {
                $summary['pending_review']++;
            }
        }

        return $summary;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    protected function rekap(array $rows): array
    {
        $perMember = [];

        foreach ($rows as $row) {
            $key = $row['user_id'];

            if (! isset($perMember[$key])) {
                $perMember[$key] = [
                    'name' => $row['name'],
                    'division' => $row['division'],
                    'location' => $row['location'],
                    'scheduled' => 0, 'present' => 0, 'late' => 0,
                    'leave' => 0, 'absent' => 0, 'not_finalized' => 0,
                    'pending_review' => 0,
                ];
            }

            $perMember[$key]['scheduled']++;

            if ($row['status'] === 'terlambat') {
                $perMember[$key]['late']++;
                $perMember[$key]['present']++;
            } elseif ($row['status'] === 'hadir') {
                $perMember[$key]['present']++;
            } elseif (in_array($row['status'], ['izin', 'sakit', 'cuti'], true)) {
                $perMember[$key]['leave']++;
            } elseif ($row['status'] === 'belum_absen') {
                $perMember[$key]['not_finalized']++;
            } else {
                $perMember[$key]['absent']++;
            }

            if (in_array('perlu_ditinjau', $row['flags'], true)) {
                $perMember[$key]['pending_review']++;
            }
        }

        return array_values($perMember);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function renderPdfPart(array $data): string
    {
        $html = view('reports.pdf', $data)->render();

        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('chroot', base_path());

        $pdf = new Dompdf($options);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        return $pdf->output();
    }

    /**
     * Menyatukan bagian-bagian PDF menjadi satu berkas dengan nomor halaman
     * absolut, supaya penomoran tidak dimulai ulang per bagian.
     *
     * @param  array<int, string>  $parts
     */
    protected function mergePdfParts(array $parts, string $monthLabel): string
    {
        $merged = new LaporanPdf;
        $merged->judulKiri = 'Laporan presensi '.$monthLabel;
        $merged->SetAutoPageBreak(false);
        $merged->SetMargins(14, 14, 14);
        $merged->AliasNbPages();

        $sources = array_map(function (string $part) {
            $path = tempnam(sys_get_temp_dir(), 'hadirin-pdf');
            file_put_contents($path, $part);

            return $path;
        }, $parts);

        try {
            foreach ($sources as $path) {
                $pageCount = $merged->setSourceFile($path);

                for ($page = 1; $page <= $pageCount; $page++) {
                    $template = $merged->importPage($page);
                    $size = $merged->getTemplateSize($template);
                    $merged->AddPage('L', [$size['width'], $size['height']]);
                    $merged->useTemplate($template);
                }
            }

            return $merged->Output('S');
        } finally {
            foreach ($sources as $path) {
                @unlink($path);
            }
        }
    }

    /**
     * Sel yang diawali karakter formula dieksekusi Excel saat berkas dibuka.
     * Awali tanda kutip tunggal supaya teks tetap terbaca sebagai teks.
     */
    protected static function safeCell(string $value): string
    {
        if ($value === '' || ! in_array($value[0], ['=', '+', '-', '@', "\t"], true)) {
            return $value;
        }

        return "'".$value;
    }
}
