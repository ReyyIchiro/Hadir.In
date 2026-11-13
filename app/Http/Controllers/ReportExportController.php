<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Location;
use App\Services\ReportService;
use App\Support\Flags;
use App\Support\TenantContext;
use App\Support\Waktu;
use Carbon\CarbonImmutable;
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

        $validated = $request->validate([
            'month' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'division_id' => ['nullable', 'integer'],
            'location_id' => ['nullable', 'integer'],
        ], [
            'month.required' => 'Bulan wajib diisi.',
            'month.regex' => 'Format bulan harus YYYY-MM.',
        ]);

        // createFromFormat menyisakan jam saat ini; bulan selalu dinormalisasi
        // ke awal bulan supaya batas bawah dan atas tidak tergantung jam ekspor.
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

        $data = $reports->monthData(
            $tenant,
            $validated['month'],
            ! empty($validated['division_id']) ? (int) $validated['division_id'] : null,
            ! empty($validated['location_id']) ? (int) $validated['location_id'] : null,
            $membership,
        );

        $filename = 'laporan-presensi-'.$validated['month'].'.csv';

        return response()->streamDownload(function () use ($data) {
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

            foreach ($data['rows'] as $row) {
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
