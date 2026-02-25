<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;
use App\Models\Kwitansi;
use Illuminate\Support\Facades\Storage;

class KwitansiController extends Controller
{
    public function generatePDF($id)
    {
        $kwitansi = Kwitansi::find($id);

        // Path ke template PDF
        $templatePath = public_path('templates/kwitansi/kwitansi_01.pdf');

        // Konversi ukuran pixel ke milimeter (misalnya, 72 DPI)
        $width = 155.22;  // 440 pixels = 155.22 mm
        $height = 279.64; // 792 pixels = 279.64 mm

        // Membuat instance FPDI
        $pdf = new FPDI();

        // Menambahkan halaman baru dengan ukuran kertas custom
        $pdf->AddPage('P', [$width, $height]);  // 'P' untuk potrait, atau 'L' untuk landscape

        // Set source file
        $pdf->setSourceFile($templatePath);
        $templateId = $pdf->importPage(1);

        // Menggunakan template pada ukuran yang telah dikonversi
        $pdf->useTemplate($templateId, 0, 0, $width, $height);  // Sesuaikan ukuran dengan ukuran kertas custom


        // Mengatur font
        //$pdf->SetFont('Helvetica');
        //$pdf->SetTextColor(0, 0, 0);

        $pdf->SetXY(21, 92);
        $pdf->SetFont('Helvetica', '', 12); 
        $pdf->MultiCell(120, 5, 'Telah kami terima ' . $kwitansi->nama_donasi . ' Sahabat LAZ Al Bahjah:', 0, 'L');

        // Menambahkan data ke template PDF
        $pdf->SetFont('Helvetica', 'B', 12); // Font bold dengan ukuran 12
        //$pdf->SetTextColor(255, 255, 255); // Warna putih
        $pdf->SetXY(33, 110); // Koordinat X dan Y untuk posisi data
        $pdf->Write(0,'Nama: '. $kwitansi->nama_donatur);

        $pdf->SetFont('Helvetica', 'B', 10); // Font normal dengan ukuran 10
        $pdf->SetTextColor(255, 255, 255); // Warna putih
        $pdf->SetXY(33, 130);
        $pdf->Write(0, '' . $kwitansi->tanggal);

        $pdf->SetFont('Helvetica', 'B',18); // Font normal dengan ukuran 10
        $pdf->SetTextColor(255, 255, 255); // Warna putih
        $pdf->SetXY(60, 131);
        $pdf->Write(0, 'Rp. ' . number_format($kwitansi->jumlah_donasi, 2));

        $pdf->SetFont('Helvetica', '',10); // Font normal dengan ukuran 10
        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(60, 142);
        $pdf->Write(0, 'Nomor Kwitansi: ' . $kwitansi->nomor_kwitansi);

        $pdf->SetFont('Helvetica', 'B',10); // Font normal dengan ukuran 10
        $pdf->SetXY(33, 161);
        $pdf->Write(0, '' . $kwitansi->nama_donasi);

        // Output PDF
        return $pdf->Output('I', 'kwitansi_' . $kwitansi->nomor_kwitansi . '.pdf');
    }
}
