<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\LaporanPerolehan;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class CSFridayPerformanceWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Perolehan Customer Service (CS) - Hari Jumat';
    
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
    
        // Query untuk menghitung total perolehan per CS
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
            ->whereRaw('WEEKDAY(tanggal) = 4')
            ->groupBy('nama_cs') // Grouping berdasarkan nama CS
            ->orderBy('total_perolehan', 'asc') // Urutkan dari kecil ke besar
            ->get();
    
        $totalPerolehan = $data->pluck('total_perolehan')->toArray();
    
        // Fungsi untuk menghasilkan gradasi hijau terang ke hijau gelap
        $backgroundColors = array_map(function ($value) use ($totalPerolehan) {
            $maxValue = max($totalPerolehan);
            $percentage = $value / ($maxValue ?: 1); // Hindari pembagian dengan 0
    
            // Warna hijau dari terang ke gelap
            $lightGreen = [106, 186, 144]; // RGB untuk warna hijau terang
            $darkGreen = [17, 117, 84];    // RGB untuk warna hijau gelap
    
            $red = (int)(($darkGreen[0] - $lightGreen[0]) * $percentage + $lightGreen[0]);
            $green = (int)(($darkGreen[1] - $lightGreen[1]) * $percentage + $lightGreen[1]);
            $blue = (int)(($darkGreen[2] - $lightGreen[2]) * $percentage + $lightGreen[2]);
    
            return "rgb($red, $green, $blue)";
        }, $totalPerolehan);
    
        // Format data menjadi rupiah
        $formattedPerolehan = array_map(function ($value) {
            return 'Rp ' . number_format($value, 0, ',', '.');
        }, $totalPerolehan);
    
        // Mengatur data untuk chart
        return [
            'datasets' => [
                [
                    'label' => 'Total Perolehan (Rupiah)',
                    'data' => $totalPerolehan,
                    'backgroundColor' => $backgroundColors, // Gunakan gradasi hijau
                ],
            ],
            'labels' => $data->pluck('nama_cs')->toArray(), // Ambil nama CS
            'options' => [
                'scales' => [
                    'x' => [
                        'ticks' => [
                            'autoSkip' => false,
                            'maxRotation' => 45, // Rotasi label sumbu X 45 derajat
                            'minRotation' => 45,
                        ]
                    ],
                    'y' => [
                        'ticks' => [
                            'callback' => function ($value) {
                                return 'Rp ' . number_format($value, 0, ',', '.'); // Format sumbu Y sebagai rupiah
                            }
                        ]
                    ]
                ]
            ]
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

