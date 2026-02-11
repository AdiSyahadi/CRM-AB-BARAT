<?php

namespace App\Filament\Resources\DataPegawaiResource\Pages;

use App\Filament\Resources\DataPegawaiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataPegawais extends ListRecords
{
    protected static string $resource = DataPegawaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('TAMBAH DATA PEGAWAI') // ← Ganti label di sini
                ->icon('heroicon-m-plus'),    // ← Tambahkan ikon jika ingin
        ];
    }
}
