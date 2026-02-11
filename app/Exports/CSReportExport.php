<?php

namespace App\Exports;

use App\Models\LaporanPerolehan; // Ganti dengan model Anda
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CSReportExport
{
    public static function export($query)
    {
        // Ambil data berdasarkan query
        $data = $query->get();

        // Inisialisasi spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header kolom
        $headers = [
            'ID', 'Tanggal', 'Tim', 'DID', 'Nama CS', 'Perolehan Jam', 'Jumlah Database', 
            'Jumlah Perolehan', 'Nama Donatur', 'Kode Negara', 'No HP', 'Program Utama', 
            'Program Zakat', 'Program Cross Selling', 'Nama Produk', 'Nama Platform', 
            'Kategori Donatur', 'Jenis Kelamin', 'Email', 'Sosial Media Account', 
            'Alamat', 'Program', 'Channel', 'Fundraiser', 'Keterangan', 'Updated At', 'Created At'
        ];

        // Isi header ke spreadsheet
        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }

        // Isi data
        $rowIndex = 2; // Mulai dari baris kedua
        foreach ($data as $row) {
            $sheet->fromArray(array_values($row->toArray()), null, "A{$rowIndex}");
            $rowIndex++;
        }

        // Simpan dan kirim ke browser
        $fileName = 'laporan_cs.xlsx';
        $writer = new Xlsx($spreadsheet);
        $tempFilePath = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFilePath);

        return response()->download($tempFilePath, $fileName)->deleteFileAfterSend();
    }
}
