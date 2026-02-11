<?php

namespace App\Filament\Widgets;
use App\Models\kencleng;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;


class kenclengs extends BaseWidget
{
        protected function getCards(): array
    {
        // Menghitung total perolehan dari semua kencleng
        $totalPerolehan = Kencleng::sum('perolehan');

        // Menghitung perolehan harian (realtime) berdasarkan tanggal pendaftaran hari ini
        $perolehanHarian = Kencleng::whereDate('tanggal_pendaftaran', now()->toDateString())->sum('perolehan');

        // Menghitung jumlah toko unik
        $jumlahToko = Kencleng::distinct('nama_toko')->count('nama_toko');

        return [
            Stat::make('Total Perolehan', 'Rp ' . number_format($totalPerolehan, 0, ',', '.'))
                ->description('Total perolehan dari semua kencleng')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('Perolehan Harian', 'Rp ' . number_format($perolehanHarian, 0, ',', '.'))
                ->description('Perolehan hari ini (' . now()->format('d F Y') . ')')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Jumlah Toko', $jumlahToko)
                ->description('Total toko yang terdaftar')
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->color('primary'),
        ];
    }
}
