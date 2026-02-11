<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters; 
use Filament\Widgets\ChartWidget;
use App\Models\LaporanPerolehan;

class TimPerformanceWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Total Perolehan TIM';
    
    protected function getData(): array
    {
        $start = $this->filters['created_at'] ?? null;
        $end = $this->filters['updated_at'] ?? null;

        // Query data
        $data = LaporanPerolehan::selectRaw('tim, SUM(jml_perolehan) as total_perolehan')
            ->when($start, fn($query) => $query->whereDate('tanggal', '>=', $start))
            ->when($end, fn($query) => $query->whereDate('tanggal', '<=', $end))
            ->groupBy('tim')
            ->orderBy('total_perolehan', 'asc')
            ->get();

        // Ambil total perolehan & label tim
        $totalPerolehan = $data->pluck('total_perolehan')->toArray();
        $timLabels = $data->pluck('tim')->toArray();

        // Palet warna berbeda signifikan (akan diulang jika tim > warna)
        $colorPalette = [
            '#f53b57', // merah terang
            '#3c40c6', // biru royal
            '#0fbcf9', // biru muda
            '#00d8d6', // toska
            '#05c46b', // hijau terang
            '#ffdd59', // kuning
            '#ffa801', // oranye terang
            '#1e272e', // hitam kebiruan
            '#575fcf', // biru keunguan
            '#ffc048', // oranye kuning
            '#34e7e4', // aqua
            '#706fd3', // ungu
            '#f19066', // salmon
            '#f7f1e3', // putih tulang
        ];

        // Ambil warna sesuai jumlah data
        $backgroundColors = array_slice(
            array_merge($colorPalette, $colorPalette),
            0,
            count($totalPerolehan)
        );

        return [
            'datasets' => [
                [
                    'label' => 'Total Perolehan',
                    'data' => $totalPerolehan,
                    'backgroundColor' => $backgroundColors,
                    'hoverOffset' => 10,
                ],
            ],
            'labels' => $timLabels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
