<?php

namespace App\Filament\Widgets;

use App\Models\LaporanPerolehan;
use Filament\Widgets\LineChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Carbon\Carbon;

class DailyPerformanceWidget extends LineChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Perolehan Harian';

    protected function getData(): array
    {
        Carbon::setLocale('id');

        // Tentukan tanggal awal dan akhir untuk bulan berjalan
        $start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $end = Carbon::now()->endOfMonth()->format('Y-m-d');
        $nama_cs = $this->filters['nama_cs'] ?? null;
        $perolehan_jam = $this->filters['perolehan_jam'] ?? null;
        $tim = $this->filters['tim'] ?? null;
        $prg_cross_selling = $this->filters['prg_cross_selling'] ?? null;
        $nama_produk = $this->filters['nama_produk'] ?? null;
        $hasil_dari = $this->filters['hasil_dari'] ?? null;
        $zakat = $this->filters['zakat'] ?? null;
        $program_utama = $this->filters['program_utama'] ?? null;

        // Dapatkan data perolehan harian untuk bulan berjalan
        $data = LaporanPerolehan::selectRaw('DATE(tanggal) as tanggal, SUM(jml_perolehan) as total_perolehan')
            ->when($nama_cs, fn($query) => $query->where('nama_cs', $nama_cs))
            ->when($tim, fn($query) => $query->where('tim', $tim))
            ->when($perolehan_jam, fn($query) => $query->where('perolehan_jam', $perolehan_jam))
            ->when($prg_cross_selling, fn($query) => $query->where('prg_cross_selling', $prg_cross_selling))
            ->when($nama_produk, fn($query) => $query->where('nama_produk', $nama_produk))
            ->when($hasil_dari, fn($query) => $query->where('hasil_dari', $hasil_dari))
            ->when($zakat, fn($query) => $query->where('zakat', $zakat))
            ->when($program_utama, fn($query) => $query->where('program_utama', $program_utama))
            ->whereDate('tanggal', '>=', $start)
            ->whereDate('tanggal', '<=', $end)
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        // Format data untuk chart
        $labels = $data->pluck('tanggal')->map(function ($date) {
            return Carbon::parse($date)->translatedFormat('D, j M'); // Nama hari singkat dan bulan singkat
        })->toArray();

        $values = $data->pluck('total_perolehan')->toArray();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Perolehan Harian',
                    'data' => $values,
                    'borderColor' => '#00FFFF', // Cyan terang untuk garis
                    'backgroundColor' => 'rgba(0, 255, 255, 0.2)', // Transparan cyan untuk area bawah
                    'fill' => true,
                    'tension' => 0.4, // Garis melengkung
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'scales' => [
                    'x' => [
                        'grid' => ['color' => 'rgba(200, 200, 200, 0.1)'], // Grid abu lembut
                        'ticks' => ['color' => '#D3D3D3'], // Label abu terang
                    ],
                    'y' => [
                        'grid' => ['color' => 'rgba(200, 200, 200, 0.1)'], // Grid abu lembut
                        'ticks' => ['color' => '#D3D3D3'], // Label abu terang
                    ],
                ],
                'plugins' => [
                    'legend' => [
                        'labels' => ['color' => '#FFFFFF'], // Putih untuk teks legenda
                    ],
                    'tooltip' => [
                        'backgroundColor' => '#333333', // Tooltip abu gelap
                        'titleColor' => '#FFFFFF', // Judul tooltip putih
                        'bodyColor' => '#D3D3D3', // Isi tooltip abu terang
                    ],
                ],
            ],
        ];
    }
}
