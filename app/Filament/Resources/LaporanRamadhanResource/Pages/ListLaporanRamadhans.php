<?php

namespace App\Filament\Resources\LaporanRamadhanResource\Pages;

use App\Filament\Resources\LaporanRamadhanResource;
use Filament\Actions;
use App\Filament\Widgets\ramadhanwidgets;
use Filament\Resources\Pages\ListRecords;

class ListLaporanRamadhans extends ListRecords
{
    protected static string $resource = LaporanRamadhanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            ramadhanwidgets::class
        ];
    }
}
