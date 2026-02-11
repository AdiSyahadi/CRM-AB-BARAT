<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\LaporanPerolehan;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TimFridayPerformanceWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'TOTAL Perolehan TIM - Hari Jumat';

    protected function getData(): array
    {
        // Mengambil data filter dari page
        $start = $this->filters['created_at'] ?? null;
        $end = $this->filters['updated_at'] ?? null;
        $perolehan_jam = $this->filters['perolehan_jam'] ?? null;
        $prg_cross_selling = $this->filters['prg_cross_selling'] ?? null;
        $nama_produk = $this->filters['nama_produk'] ?? null;
        $hasil_dari = $this->filters['hasil_dari'] ?? null;
        $zakat = $this->filters['zakat'] ?? null;

        // Query untuk menghitung total perolehan per TIM
        $data = LaporanPerolehan::selectRaw('tim, SUM(jml_perolehan) as total_perolehan')
            ->when($start, fn($query) => $query->whereDate('tanggal', '>=', $start))
            ->when($end, fn($query) => $query->whereDate('tanggal', '<=', $end))
            ->when($perolehan_jam, fn($query) => $query->where('perolehan_jam', $perolehan_jam))
            ->when($hasil_dari, fn($query) => $query->where('hasil_dari', $hasil_dari))
            ->when($prg_cross_selling, fn($query) => $query->where('prg_cross_selling', $prg_cross_selling))
            ->when($nama_produk, fn($query) => $query->where('nama_produk', $nama_produk))
            ->when($zakat, fn($query) => $query->where('zakat', $zakat))
            ->groupBy('tim')
            ->orderBy('total_perolehan', 'asc')
            ->get();

        $totalPerolehan = $data->pluck('total_perolehan')->toArray();

        // Format data menjadi rupiah untuk label (jika ingin dipakai nanti)
        $formattedPerolehan = array_map(function ($value) {
            return 'Rp ' . number_format($value, 0, ',', '.');
        }, $totalPerolehan);

        // Warna-warna kontras tinggi untuk membedakan tiap TIM dengan jelas
        $colorPalette = [
            '#FF6384', // Merah
            '#36A2EB', // Biru
            '#FFCE56', // Kuning
            '#4BC0C0', // Toska
            '#9966FF', // Ungu
            '#FF9F40', // Oranye
            '#C9CBCF', // Abu-abu
            '#00A676', // Hijau Emerald
            '#FF6B6B', // Merah Muda Cerah
            '#3E517A', // Biru Tua
        ];

        // Ambil warna sesuai jumlah data, ulangi palet jika lebih dari 10 tim
        $backgroundColor = array_slice(
            array_merge($colorPalette, $colorPalette),
            0,
            count($totalPerolehan)
        );

        // Mengatur data untuk doughnut chart
        return [
            'datasets' => [
                [
                    'data' => $totalPerolehan,
                    'backgroundColor' => $backgroundColor,
                    'hoverOffset' => 10,
                ],
            ],
            'labels' => $data->pluck('tim')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
