<?php

namespace App\Filament\Resources\AnalisisDonaturResource\Pages;

use App\Filament\Resources\AnalisisDonaturResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Filament\Tables\Contracts\HasTable;

class ListAnalisisDonatur extends ListRecords
{
    protected static string $resource = AnalisisDonaturResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * Override untuk menggunakan no_hp sebagai record key
     */
    public function getTableRecordKey($record): string
    {
        return (string) ($record->no_hp ?? $record->id ?? '');
    }

    protected function getTableQuery(): Builder
    {
        // Base query dengan agregasi
        $query = \App\Models\LaporanPerolehan::query()
            ->select(
                'no_hp',
                DB::raw('MAX(nama_donatur) as nama_donatur'),
                DB::raw('SUM(jml_perolehan) as total_donasi'),
                DB::raw('COUNT(*) as jml_transaksi'),
                DB::raw('MIN(tanggal) as first_donation'),
                DB::raw('MAX(tanggal) as last_donation')
            )
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '')
            ->groupBy('no_hp');

        return $query;
    }

    public function getTitle(): string
    {
        return 'Analisis Donatur';
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
