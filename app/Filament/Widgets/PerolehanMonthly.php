<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\LaporanPerolehan;

class PerolehanMonthly extends ChartWidget
{
    protected static ?string $heading = 'Perolehan Bulanan';
    use InteractsWithPageFilters;

    // Mengatur lebar widget menjadi 'full'
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Mengambil data filter dari page
        //$start = $this->filters['created_at'] ?? null;
        //$end = $this->filters['updated_at'] ?? null;
        $nama_cs = $this->filters['nama_cs'] ?? null;
        $perolehan_jam = $this->filters['perolehan_jam'] ?? null;
        $tim = $this->filters['tim'] ?? null;
        $prg_cross_selling = $this->filters['prg_cross_selling'] ?? null;
        $nama_produk = $this->filters['nama_produk'] ?? null;
        $hasil_dari = $this->filters['hasil_dari'] ?? null;
        $zakat = $this->filters['zakat'] ?? null;

        // Query untuk mendapatkan total perolehan per bulan dengan filter
        $data = LaporanPerolehan::selectRaw('EXTRACT(MONTH FROM tanggal) as bulan, SUM(jml_perolehan) as total_perolehan')
            //->when($start, function ($query) use ($start) {
                //return $query->whereDate('created_at', '>=', $start);
            //})
            //->when($end, function ($query) use ($end) {
                //return $query->whereDate('updated_at', '<=', $end);
            //})
            ->when($nama_cs, function ($query) use ($nama_cs) {
                return $query->where('nama_cs', $nama_cs);
            })
            ->when($perolehan_jam, function ($query) use ($perolehan_jam) {
                return $query->where('perolehan_jam', $perolehan_jam);
            })
            ->when($perolehan_jam, function ($query) use ($perolehan_jam) {
                return $query->where('perolehan_jam', $perolehan_jam);
            })
            ->when($tim, function ($query) use ($tim) {
                return $query->where('tim', $tim);
            })
            ->when($hasil_dari, function ($query) use ($hasil_dari) {
                return $query->where('hasil_dari', $hasil_dari);
            })
            ->when($prg_cross_selling, function ($query) use ($prg_cross_selling) {
                return $query->where('prg_cross_selling', $prg_cross_selling);
            })
            ->when($nama_produk, function ($query) use ($nama_produk) {
                return $query->where('nama_produk', $nama_produk);
            })
            ->when($zakat, function ($query) use ($zakat) {
                return $query->where('zakat', $zakat);
            })
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // Ambil data perolehan dan bulan untuk digunakan dalam chart
        $labels = $data->pluck('bulan')->toArray();
        $totals = $data->pluck('total_perolehan')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Perolehan',
                    'data' => $totals, // Data perolehan per bulan
                    'borderColor' => 'rgba(75, 192, 192, 1)', // Warna garis
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)', // Warna latar belakang area di bawah garis
                    'fill' => true, // Isi area di bawah garis
                    'tension' => 0.4, // Membuat garis menjadi lebih halus
                ],
            ],
            'labels' => array_map(function($month) {
                // Ubah angka bulan menjadi nama bulan
                return date("F", mktime(0, 0, 0, $month, 10));
            }, $labels),
        ];
    }

    protected function getType(): string
    {
        return 'line'; // Line chart
    }
}
