<?php

namespace App\Filament\Widgets;

use App\Models\LaporanPerolehan;
use Filament\Widgets\BarChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class CSPerformanceWidget extends BarChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Total Perolehan Customer Service (CS)';
    
    // Mengatur lebar widget menjadi 'full'
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Mengambil data filter dari page
        $start = $this->filters['created_at'] ?? null;
        $end = $this->filters['updated_at'] ?? null;
        $perolehan_jam = $this->filters['perolehan_jam'] ?? null;
        $tim = $this->filters['tim'] ?? null;
        $prg_cross_selling = $this->filters['prg_cross_selling'] ?? null;
        $nama_produk = $this->filters['nama_produk'] ?? null;
        $hasil_dari = $this->filters['hasil_dari'] ?? null;
        $zakat = $this->filters['zakat'] ?? null;

        // Query untuk menghitung total perolehan per CS dan mengurutkan secara ascending (kecil ke besar)
        $data = LaporanPerolehan::selectRaw('nama_cs, SUM(jml_perolehan) as total_perolehan')
            ->when($start, fn($query) => $query->whereDate('tanggal', '>=', $start))
            ->when($end, fn($query) => $query->whereDate('tanggal', '<=', $end))
            ->when($perolehan_jam, fn($query) => $query->where('perolehan_jam', $perolehan_jam))
            ->when($tim, fn($query) => $query->where('tim', $tim))
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
            ->groupBy('nama_cs')
            ->orderBy('total_perolehan', 'asc') // Mengurutkan berdasarkan total perolehan
            ->get();

        $totalPerolehan = $data->pluck('total_perolehan')->toArray();

        
        $backgroundColors = array_map(function ($value) use ($totalPerolehan) {
            $maxValue = max($totalPerolehan);
            $percentage = $value / $maxValue;

            // Warna-warna gradasi: #001F3F, #3A6D8C, #6A9AB0
            $darkColor = [0, 31, 63];    // RGB dari #001F3F
            $midColor = [58, 109, 140];  // RGB dari #3A6D8C
            $lightColor = [106, 154, 176]; // RGB dari #6A9AB0

            if ($percentage < 0.5) {
                // Interpolasi antara darkColor dan midColor
                $percentage = $percentage * 2; // Skala 0-1 untuk interpolasi
                $red = (int)(($midColor[0] - $darkColor[0]) * $percentage + $darkColor[0]);
                $green = (int)(($midColor[1] - $darkColor[1]) * $percentage + $darkColor[1]);
                $blue = (int)(($midColor[2] - $darkColor[2]) * $percentage + $darkColor[2]);
            } else {
                // Interpolasi antara midColor dan lightColor
                $percentage = ($percentage - 0.5) * 2; // Skala 0-1 untuk interpolasi
                $red = (int)(($lightColor[0] - $midColor[0]) * $percentage + $midColor[0]);
                $green = (int)(($lightColor[1] - $midColor[1]) * $percentage + $midColor[1]);
                $blue = (int)(($lightColor[2] - $midColor[2]) * $percentage + $midColor[2]);
            }

            return "rgb($red, $green, $blue)";
        }, $totalPerolehan);




        // Mengatur data untuk chart
        return [
            'datasets' => [
                [
                    'label' => 'Total Perolehan',
                    'data' => $totalPerolehan,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $data->pluck('nama_cs')->toArray(), // Mengambil nama CS berdasarkan urutan perolehan
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
