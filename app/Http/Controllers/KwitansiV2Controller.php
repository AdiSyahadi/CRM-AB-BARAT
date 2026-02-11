<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;
use App\Models\Kwitansiv2; // Pastikan model yang diimpor adalah Kwitansiv2
use Illuminate\Support\Facades\Storage;

class KwitansiV2Controller extends Controller
{
    public function generatePDF($id)
    {
        // Mengambil data kwitansi dengan menggunakan model Kwitansiv2
        $kwitansiv2 = Kwitansiv2::findOrFail($id); // findOrFail akan mengembalikan 404 jika tidak ditemukan

        // Path ke template PDF
        $templatePath = storage_path('app/public/kwitansi_template/kwitansi_templatev2.pdf');

        // Konversi ukuran pixel ke milimeter (1600 x 1294 px pada 72 DPI)
            $width = 564.44;  // Lebar dalam mm
            $height = 456.49; // Tinggi dalam mm

            // Membuat instance FPDI
            $pdf = new FPDI();

            // Menambahkan halaman baru dengan ukuran kertas custom
            $pdf->AddPage('L', [$width, $height]);  // 'P' untuk potrait, atau 'L' untuk landscape
            $pdf->setSourceFile($templatePath);
            $templateId = $pdf->importPage(1);
            $pdf->useTemplate($templateId, 0, 0, $width, $height); // Sesuaikan ukuran dengan milimeter
// Sesuaikan ukuran dengan milimeter


        // Mengatur font
        $pdf->SetFont('Helvetica', 'B', 18); // Font bold dengan ukuran 12
        $pdf->SetTextColor(0, 0, 0); // Warna hitam

        // Menambahkan data ke template PDF
        $pdf->SetFont('Helvetica', 'B', 18); // Font bold dengan ukuran 12
        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(315, 53); // Posisi X dan Y untuk Nama Donatur
        $pdf->Write(0, '' . strtoupper($kwitansiv2->nama_donatur)); // Mengubah ke huruf kapital
        
        $pdf->SetFont('Helvetica', 'B', 18); // Font bold dengan ukuran 12
        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(315, 72); // Posisi X dan Y untuk Tanggal
        $pdf->Write(0, '' . $kwitansiv2->tanggal);

        $pdf->SetFont('Helvetica', 'B', 18); // Font bold dengan ukuran 12
        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(315, 91); // Posisi X dan Y untuk Alamat
        $pdf->Write(0, '' . $kwitansiv2->alamat);

        $pdf->SetFont('Helvetica', 'B', 18); // Font bold dengan ukuran 12
        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(315, 109); // Posisi X dan Y untuk Telepon
        $pdf->Write(0, '' . $kwitansiv2->telepon);

        $pdf->SetFont('Helvetica', 'B', 24); // Font bold dengan ukuran 12
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY(10, 50); // Posisi X dan Y untuk Nomor Kwitansi
        $pdf->Write(0, 'Nomor : ' . $kwitansiv2->nomor_kwitansi);

        $pdf->SetFont('Helvetica', 'B', 26); // Font bold dengan ukuran 12
        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(250, 163); // Posisi X dan Y untuk Nama Donasi
        $pdf->Write(0, '' . strtoupper($kwitansiv2->nama_donasi)); // Mengubah ke huruf kapital

        $pdf->SetFont('Helvetica', 'B', 26); // Font bold dengan ukuran 26
        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(390, 163); // Posisi X dan Y untuk Jumlah Donasi

        // Periksa apakah 'jumlah_donasi' ada dan tidak null
        if (!empty($kwitansiv2->jumlah_donasi)) {
            $pdf->Write(0, 'Rp. ' . number_format($kwitansiv2->jumlah_donasi));
        }


        $pdf->SetFont('Helvetica', 'B', 26); // Font bold dengan ukuran 12
        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(250, 186); // Posisi X dan Y untuk Nama Donasi
        $pdf->Write(0, '' . strtoupper($kwitansiv2->nama_donasi2)); // Mengubah ke huruf kapital

        $pdf->SetFont('Helvetica', 'B', 26); // Font bold dengan ukuran 26
        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(390, 186); // Posisi X dan Y untuk Jumlah Donasi

        // Periksa apakah 'jumlah_donasi2' ada dan tidak null
        if (!empty($kwitansiv2->jumlah_donasi2)) {
            $pdf->Write(0, 'Rp. ' . number_format($kwitansiv2->jumlah_donasi2));
        }


        $pdf->SetFont('Helvetica', 'B', 26); // Font bold dengan ukuran 12
        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(250, 210); // Posisi X dan Y untuk Nama Donasi
        $pdf->Write(0, '' . strtoupper($kwitansiv2->nama_donasi3)); // Mengubah ke huruf kapital

        $pdf->SetFont('Helvetica', 'B', 26); // Font bold dengan ukuran 26
        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(390, 210); // Posisi X dan Y untuk Jumlah Donasi

        // Periksa apakah 'jumlah_donasi3' ada dan tidak null
        if (!empty($kwitansiv2->jumlah_donasi3)) {
            $pdf->Write(0, 'Rp. ' . number_format($kwitansiv2->jumlah_donasi3));
        }


        $pdf->SetFont('Helvetica', 'B', 26); // Font bold dengan ukuran 12
        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(250, 235); // Posisi X dan Y untuk Nama Donasi
        $pdf->Write(0, '' . strtoupper($kwitansiv2->nama_donasi4)); // Mengubah ke huruf kapital

        $pdf->SetFont('Helvetica', 'B', 26); // Font bold dengan ukuran 26
        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(390, 235); // Posisi X dan Y untuk Jumlah Donasi

        // Periksa apakah 'jumlah_donasi4' ada dan tidak null
        if (!empty($kwitansiv2->jumlah_donasi4)) {
            $pdf->Write(0, 'Rp. ' . number_format($kwitansiv2->jumlah_donasi4));
        }


        $pdf->SetFont('Helvetica', 'B', 26); // Font bold dengan ukuran 12
        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(250, 258); // Posisi X dan Y untuk Nama Donasi
        $pdf->Write(0, '' . strtoupper($kwitansiv2->nama_donasi5)); // Mengubah ke huruf kapital

        $pdf->SetFont('Helvetica', 'B', 26); // Font bold dengan ukuran 26
        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(390, 258); // Posisi X dan Y untuk Jumlah Donasi

        // Periksa apakah 'jumlah_donasi5' ada dan tidak null
        if (!empty($kwitansiv2->jumlah_donasi5)) {
            $pdf->Write(0, 'Rp. ' . number_format($kwitansiv2->jumlah_donasi5));
        }

        $pdf->SetFont('Helvetica', 'B', 26); // Font bold dengan ukuran 12
        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        // Menyesuaikan kolom 'total_donasi' dengan benar
        $pdf->SetXY(425, 304); // Posisi X dan Y untuk Total Donasi
        $pdf->Write(0, 'Rp ' . number_format($kwitansiv2->total_donasi, ));

        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetFont('Helvetica', 'B', 14); // Font bold dengan ukuran 12
        // Menyesuaikan kolom 'total_donasi' dengan benar
        $pdf->SetXY(383, 335); // Posisi X dan Y untuk Total Donasi
        $pdf->Write(0, '' . strtoupper($kwitansiv2->terbilang));

        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(265, 410); // Posisi X dan Y untuk Diserahkan oleh
        $pdf->Write(0, '' . strtoupper($kwitansiv2->diserahkan));

        $pdf->SetTextColor(0, 0, 0); // Warna hitam
        $pdf->SetXY(420, 410); // Posisi X dan Y untuk Diterima oleh
        $pdf->Write(0, '' . strtoupper($kwitansiv2->diterima));

        // Output PDF ke browser
        return $pdf->Output('I', 'kwitansi_' . $kwitansiv2->nomor_kwitansi . '.pdf');
    }
}
