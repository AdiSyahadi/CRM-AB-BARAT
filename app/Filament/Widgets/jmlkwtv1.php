<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Kwitansi;
use Carbon\Carbon; // Tambahkan ini untuk penggunaan tanggal saat ini

class jmlkwtv1 extends BaseWidget
{
    protected function getStats(): array
    {
        // Menghitung total kwitansi yang dibuat
        $totalKwitansi = Kwitansi::count();

        // Menghitung total perolehan donasi
        $totalPerolehan = Kwitansi::sum('jumlah_donasi');

        // Mendapatkan donatur dengan kontribusi terbanyak di bulan ini
        $donaturTerbanyak = Kwitansi::select('nama_donatur')
            ->whereMonth('created_at', Carbon::now()->month) // Filter berdasarkan bulan saat ini
            ->groupBy('nama_donatur')
            ->orderByRaw('COUNT(*) DESC') // Urutkan berdasarkan jumlah donasi
            ->limit(1) // Ambil donatur yang paling banyak berkontribusi
            ->pluck('nama_donatur')
            ->first() ?? 'Tidak ada donatur'; // Jika tidak ada data donatur, tampilkan ini

        return [
            Stat::make('', $totalKwitansi)
                ->description('Total Kwitansi yang Dibuat')
                ->descriptionIcon('heroicon-m-document')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info'),

            Stat::make('', 'Rp' . number_format($totalPerolehan, 0, ',', '.'))
                ->description('Total Perolehan')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('', $donaturTerbanyak)
                ->description('Kontribusi Terbanyak')
                ->descriptionIcon('heroicon-m-star')
                ->chart([12, 15, 5, 8, 10, 7, 14])
                ->color('warning'),
        ];
    }
}
