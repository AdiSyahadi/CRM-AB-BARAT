<?php

namespace App\Filament\Widgets;

use App\Models\LaporanPerolehan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class RamadhanWidgets extends BaseWidget
{
    protected function getStats(): array
    {
        $totalPerolehan2024 = LaporanPerolehan::whereBetween('tanggal', ['2024-03-12', '2024-04-10'])
            ->sum('jml_perolehan');

        $totalPerolehan2025 = LaporanPerolehan::whereBetween('tanggal', ['2025-03-01', '2025-04-01'])
            ->sum('jml_perolehan');

        $selisihPerolehan = 1800000000 - $totalPerolehan2025; // Hitung selisih

        return [
            Stat::make('Total Perolehan 2024', number_format($totalPerolehan2024, 0, ',', '.'))
                ->description('Perolehan dari 12 Maret - 10 April 2024')
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('Total Perolehan 2025', number_format($totalPerolehan2025, 0, ',', '.'))
                ->description('Perolehan dari 1 Maret - 1 April 2025')
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('Selisih Perolehan 2025 (Target 1.8M)', number_format($selisihPerolehan, 0, ',', '.'))
                ->description('Selisih perolehan 2025 dibandingkan 1.8 M')
                ->icon($selisihPerolehan < 0 ? 'heroicon-o-arrow-down' : 'heroicon-o-arrow-up')
                ->color($selisihPerolehan < 0 ? 'danger' : 'success'), // Warna merah jika selisih negatif
        ];
    }
}
