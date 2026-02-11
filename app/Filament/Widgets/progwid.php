<?php

namespace App\Filament\Widgets;

use App\Models\LaporanPerolehan; // Mengganti dari Laporan menjadi LaporanPerolehan
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class progwid extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $start = $this->filters['created_at'] ?? null;
        $end = $this->filters['updated_at'] ?? null;
        $nama_cs = $this->filters['nama_cs'] ?? null;
        $perolehan_jam = $this->filters['perolehan_jam'] ?? null;
        $tim = $this->filters['tim'] ?? null;
        $prg_cross_selling = $this->filters['prg_cross_selling'] ?? null;
        $nama_produk = $this->filters['nama_produk'] ?? null;
        $hasil_dari = $this->filters['hasil_dari'] ?? null;
        $zakat = $this->filters['zakat'] ?? null;

        return [
            Stat::make('', 'Rp' . number_format(LaporanPerolehan:: 
                when($start, fn($query) => $query->whereDate('tanggal', '>=', $start))
                ->when($end, fn($query) => $query->whereDate('tanggal', '<=', $end)) 
                ->when($nama_cs, function ($query) use ($nama_cs) {
                    return $query->where('nama_cs', $nama_cs);
                })
                ->when($perolehan_jam, function ($query) use ($perolehan_jam) {
                    return $query->where('perolehan_jam', $perolehan_jam);
                })
                ->when($perolehan_jam, function ($query) use ($perolehan_jam) {
                    return $query->where('perolehan_jam', $perolehan_jam);
                })
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
                ->where('tim', 'CABANG') // Mengganti dari program menjadi tim
                ->sum('jml_perolehan'), 0, ',', '.'))
            ->description('CABANG')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success'),

            Stat::make('', 'Rp' . number_format(LaporanPerolehan:: 
                when($start, fn($query) => $query->whereDate('tanggal', '>=', $start))
                ->when($end, fn($query) => $query->whereDate('tanggal', '<=', $end)) 
                ->when($nama_cs, function ($query) use ($nama_cs) {
                    return $query->where('nama_cs', $nama_cs);
                })
                ->when($perolehan_jam, function ($query) use ($perolehan_jam) {
                    return $query->where('perolehan_jam', $perolehan_jam);
                })
                ->when($perolehan_jam, function ($query) use ($perolehan_jam) {
                    return $query->where('perolehan_jam', $perolehan_jam);
                })
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
                ->where('tim', 'PRODUK') // Mengganti dari program menjadi tim
                ->sum('jml_perolehan'), 0, ',', '.'))
            ->description('PRODUK')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success'),

            Stat::make('', 'Rp' . number_format(LaporanPerolehan:: 
                when($start, fn($query) => $query->whereDate('tanggal', '>=', $start))
                ->when($end, fn($query) => $query->whereDate('tanggal', '<=', $end)) 
                ->when($nama_cs, function ($query) use ($nama_cs) {
                    return $query->where('nama_cs', $nama_cs);
                })
                ->when($perolehan_jam, function ($query) use ($perolehan_jam) {
                    return $query->where('perolehan_jam', $perolehan_jam);
                })
                ->when($perolehan_jam, function ($query) use ($perolehan_jam) {
                    return $query->where('perolehan_jam', $perolehan_jam);
                })
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
                ->where('tim', 'PLATFORM') // Mengganti dari program menjadi tim
                ->sum('jml_perolehan'), 0, ',', '.'))
            ->description('PLATFORM')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->chart([7, 2, 10, 3, 15, 4, 17])
            ->color('success'),
        ];
    }
}
