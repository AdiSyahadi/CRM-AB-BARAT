<?php

namespace App\Filament\Resources\Kwitansiv2Resource\Pages;

use App\Filament\Resources\Kwitansiv2Resource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\jmlkwt; // Pastikan mengimpor widget yang benar

class ListKwitansiv2s extends ListRecords
{
    protected static string $resource = Kwitansiv2Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            jmlkwt::class, // Tambahkan koma untuk memisahkan elemen dalam array
        ];
    }
}
