<?php

namespace App\Filament\Widgets;

use App\Models\LaporanPerolehan; // Mengganti dari Laporan menjadi LaporanPerolehan
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class jmldonatur extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $start = $this->filters['created_at'] ?? null;
        $end = $this->filters['updated_at'] ?? null;
        $perolehan_jam = $this->filters['perolehan_jam'] ?? null;
        $nama_cs = $this->filters['nama_cs'] ?? null;
        $tim = $this->filters['tim'] ?? null; // Mengganti dari program menjadi tim

        // Query untuk menghitung jumlah nomor HP unik (donatur unik)
        $uniqueDonorCount = LaporanPerolehan:: // Mengganti dari Laporan menjadi LaporanPerolehan
            when($start, fn($query) => $query->whereDate('tanggal', '>=', $start))
            ->when($end, fn($query) => $query->whereDate('tanggal', '<=', $end))
            ->when($perolehan_jam, fn($query) => $query->where('perolehan_jam', $perolehan_jam))
            ->when($nama_cs, fn($query) => $query->where('nama_cs', $nama_cs))
            ->when($tim, fn($query) => $query->where('tim', $tim)) // Filter berdasarkan tim
            ->distinct('no_hp') // Menghitung nomor HP yang unik
            ->count('no_hp');

        return [
            Stat::make('', number_format($uniqueDonorCount))
                ->description('Prospek')
                ->descriptionIcon('heroicon-o-user-group')
                ->chart([7, 5, 8, 12, 6, 15, 10]) // Contoh data chart
                ->color('primary'),
            Stat::make('', number_format($uniqueDonorCount))
                ->description('Istiqomah')
                ->descriptionIcon('heroicon-o-user-group')
                ->chart([7, 5, 8, 12, 6, 15, 10]) // Contoh data chart
                ->color('primary'),
            Stat::make('', number_format($uniqueDonorCount))
                ->description('Barokah')
                ->descriptionIcon('heroicon-o-user-group')
                ->chart([7, 5, 8, 12, 6, 15, 10]) // Contoh data chart
                ->color('primary'),
            Stat::make('', number_format($uniqueDonorCount))
                ->description('Total Donatur')
                ->descriptionIcon('heroicon-o-user-group')
                ->chart([7, 5, 8, 12, 6, 15, 10]) // Contoh data chart
                ->color('primary'),
            Stat::make('', number_format($uniqueDonorCount))
                ->description('Donatur Baru')
                ->descriptionIcon('heroicon-o-user-group')
                ->chart([7, 5, 8, 12, 6, 15, 10]) // Contoh data chart
                ->color('primary'),
        ];
    }
}
