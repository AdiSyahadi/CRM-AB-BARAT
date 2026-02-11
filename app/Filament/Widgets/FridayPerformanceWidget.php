<?php

namespace App\Filament\Widgets;

use App\Models\LaporanPerolehan; // Mengganti dari Laporan menjadi LaporanPerolehan
use Filament\Widgets\LineChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Carbon\Carbon;

class FridayPerformanceWidget extends LineChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Perolehan Tiap Hari Jumat';
    //protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $start = $this->filters['created_at'] ?? null;
        $end = $this->filters['updated_at'] ?? null;
        $nama_cs = $this->filters['nama_cs'] ?? null;
        $perolehan_jam = $this->filters['perolehan_jam'] ?? null;
        $tim = $this->filters['tim'] ?? null;
        $prg_cross_selling = $this->filters['prg_cross_selling'] ?? null;
        $nama_produk = $this->filters['nama_produk'] ?? null;
        $hasil_dari = $this->filters['hasil_dari'] ?? null;
        $zakat = $this->filters['zakat'] ?? null;

        // Mendapatkan data perolehan dari hari Jumat
        $data = LaporanPerolehan::selectRaw('DATE(tanggal) as tanggal, SUM(jml_perolehan) as total_perolehan')
            ->when($nama_cs, function ($query) use ($nama_cs) {
                return $query->where('nama_cs', $nama_cs);
            })
            ->when($start, fn($query) => $query->whereDate('tanggal', '>=', $start))
            ->when($end, fn($query) => $query->whereDate('tanggal', '<=', $end)) 
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
            }) // Filter berdasarkan tim
            ->whereRaw('WEEKDAY(tanggal) = 4') // 4 adalah hari Jumat
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        // Menyusun data untuk chart
        $labels = $data->pluck('tanggal');
        $values = $data->pluck('total_perolehan');

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Perolehan',
                    'data' => $values,
                    'borderColor' => '#4F46E5', // Warna line chart
                    'fill' => false,
                ],
            ],
        ];
    }
}
