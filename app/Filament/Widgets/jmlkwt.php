<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Kwitansi;
use App\Models\Kwitansiv2;
class jmlkwt extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('', Kwitansiv2::count())
            ->description('Total Kwitansi yang Dibuat')
            ->descriptionIcon('heroicon-m-document')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('info'),

            Stat::make('', 'Rp' . number_format(Kwitansiv2::sum('total_donasi'), 0, ',', '.'))
            ->description('Total Perolehan')
            ->descriptionIcon('heroicon-m-currency-dollar')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success'),

            Stat::make('', Kwitansiv2::select('nama_donatur')
            ->whereMonth('created_at', now()->month) // Filter berdasarkan bulan ini
            ->groupBy('nama_donatur') 
            ->orderByRaw('COUNT(*) DESC') // Urutkan berdasarkan jumlah kemunculan terbanyak
            ->limit(1) // Ambil donatur yang paling sering muncul
            ->pluck('nama_donatur')
            ->first())
            ->description('Kontribusi Terbanyak')
            ->descriptionIcon('heroicon-m-star')
            ->chart([12, 15, 5, 8, 10, 7, 14])
            ->color('warning'),

        ];
    }
}
