<?php

namespace App\Filament\Resources\KwitansiResource\Pages;

use App\Filament\Resources\KwitansiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\jmlkwtv1; // Pastikan mengimpor widget yang benar

class ListKwitansis extends ListRecords
{
    protected static string $resource = KwitansiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            jmlkwtv1::class, // Tambahkan koma untuk memisahkan elemen dalam array
        ];
    }
}
