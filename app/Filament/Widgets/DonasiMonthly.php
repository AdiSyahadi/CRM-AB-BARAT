<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\LaporanPerolehan;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class DonasiMonthly extends ChartWidget
{
    protected static ?string $heading = 'Statistik Donatur berdonasi dan Transaksi';

    use InteractsWithPageFilters;

    // Mengatur lebar widget menjadi 'full'
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Ambil filter nama_cs dan tim
        $nama_cs = $this->filters['nama_cs'] ?? null;
        $tim = $this->filters['tim'] ?? null;

        // Query untuk mendapatkan data donatur berdonasi dan jumlah transaksi
        $query = LaporanPerolehan::selectRaw('
                MONTH(tanggal) as bulan, 
                COUNT(DISTINCT no_hp) as donatur_berdonasi,
                COUNT(no_hp) as jumlah_transaksi
            ');

        // Jika filter nama_cs diberikan, tambahkan ke query
        if ($nama_cs) {
            $query->where('nama_cs', $nama_cs);
        }

        // Jika filter tim diberikan, tambahkan ke query
        if ($tim) {
            $query->where('tim', $tim);
        }

        // Grouping berdasarkan bulan
        $data = $query->groupBy('bulan')->orderBy('bulan')->get();

        // Data untuk chart
        $labels = $data->pluck('bulan')->toArray();
        $donatur = $data->pluck('donatur_berdonasi')->toArray();
        $transaksi = $data->pluck('jumlah_transaksi')->toArray();

        // Konversi angka bulan menjadi nama bulan
        $formattedLabels = array_map(function ($month) {
            return date("F", mktime(0, 0, 0, $month, 10));
        }, $labels);

        // Jika dataset kosong, kembalikan placeholder
        if (empty($donatur) && empty($transaksi)) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Donatur Berdonasi',
                    'data' => $donatur,
                    'borderColor' => 'rgba(0, 102, 204, 1)', // Biru
                    'backgroundColor' => 'rgba(0, 102, 204, 0.2)', // Transparan biru
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Jumlah Transaksi',
                    'data' => $transaksi,
                    'borderColor' => 'rgba(0, 204, 102, 1)', // Hijau
                    'backgroundColor' => 'rgba(0, 204, 102, 0.2)', // Transparan hijau
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $formattedLabels,
        ];
        
    }

    protected function getType(): string
    {
        return 'line'; // Line chart
    }
}
