<?php

namespace App\Support;

use setasign\Fpdi\Fpdi;

/**
 * Penyatu PDF bagian laporan. FPDF memanggil Footer() setiap halaman baru
 * ditambahkan, dan alias {nb} diganti jumlah halaman saat Output(), jadi
 * nomor halaman tetap benar walau bagian digabung bertahap.
 */
class LaporanPdf extends Fpdi
{
    public string $judulKiri = '';

    public function Footer(): void
    {
        $margin = 14.0;
        $y = $this->GetPageHeight() - 10.0;

        $this->SetY($y);
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(70, 70, 70);
        $this->SetX($margin);
        $this->Cell(0, 4, $this->judulKiri);
        $this->SetX($margin);
        $this->Cell($this->GetPageWidth() - 2 * $margin, 4, 'Halaman '.$this->PageNo().' / {nb}', 0, 0, 'R');
    }
}
