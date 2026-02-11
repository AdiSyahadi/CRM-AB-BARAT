<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\LaporanPerolehan;
use Carbon\Carbon;

class lapharwid extends BaseWidget
{
    protected function getStats(): array
    {
         // Mendapatkan tanggal hari ini
         $today = Carbon::today();
        return [
            Stat::make('', 'Rp' . number_format(
                LaporanPerolehan::query()
                    ->whereDate('tanggal', '=', $today) // Filter data hari ini
                    ->sum('jml_perolehan'), 0, ',', '.'))
                ->description('Jumlah Perolehan Hari Ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([10, 15, 20, 25, 30, 35, 40])
                ->color('info'),

            Stat::make('', 'Rp' . number_format(
                LaporanPerolehan::query()
                    ->whereDate('tanggal', '=', $today) // Filter data hari ini
                    ->where('tim', 'AB BARAT') // Filter untuk tim AB BARAT
                    ->sum('jml_perolehan'), 0, ',', '.'))
                ->description('AL-BAHJAH BARAT')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('', 'Rp' . number_format(
                LaporanPerolehan::query()
                    ->whereDate('tanggal', '=', $today) // Filter data hari ini
                    ->where('tim', 'WAKAF') // Filter untuk tim WAKAF
                    ->sum('jml_perolehan'), 0, ',', '.'))
                ->description('WAKAF')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('', 'Rp' . number_format(
                LaporanPerolehan::query()
                ->whereDate('tanggal', '=', $today) // Filter data hari ini
                ->where('tim', 'CABANG') // Mengganti dari program menjadi tim
                ->sum('jml_perolehan'), 0, ',', '.'))
                ->description('CABANG')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
        ];
    }
}
