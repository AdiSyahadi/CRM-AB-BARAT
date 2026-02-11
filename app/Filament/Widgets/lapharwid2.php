<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\LaporanPerolehan;
use Carbon\Carbon;

class lapharwid2 extends BaseWidget
{
    protected function getStats(): array
    {
         // Mendapatkan tanggal hari ini
         $today = Carbon::today();
        return [
            Stat::make('', 'Rp' . number_format(
            LaporanPerolehan::query()
            ->whereDate('tanggal', '=', $today) // Filter data hari ini
            ->where('tim', 'PRODUK') // Mengganti dari program menjadi tim
            ->sum('jml_perolehan'), 0, ',', '.'))
            ->description('PRODUK')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success'),

            Stat::make('', 'Rp' . number_format(
            LaporanPerolehan::query()
            ->whereDate('tanggal', '=', $today) // Filter data hari ini
            ->where('tim', 'PLATFORM') // Mengganti dari program menjadi tim
            ->sum('jml_perolehan'), 0, ',', '.'))
            ->description('PLATFORM')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success'),

            Stat::make('', 'Rp' . number_format(
                LaporanPerolehan::query()
                    ->whereDate('tanggal', '=', $today) // Filter data hari ini
                    ->selectRaw('SUM(CASE WHEN prg_cross_selling IS NOT NULL THEN jml_perolehan ELSE 0 END) as total_cross_selling')
                    ->value('total_cross_selling'), 0, ',', '.'))
                ->description('CROSS SELLING')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([5, 8, 12, 7, 18, 6, 20])
                ->color('success'),
            Stat::make('', number_format(
                LaporanPerolehan::query()
                    ->whereDate('tanggal', '=', $today) // Filter data hari ini
                    ->sum('jml_database'), 0, ',', '.'))
                    ->description('TOTAL DATABASE')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->chart([10, 15, 20, 25, 30, 35, 40])
                    ->color('info'),
        ];
    }
}
