<?php

namespace App\Filament\Resources\LaporanramadhantimResource\Pages;

use App\Filament\Resources\LaporanramadhantimResource;
use Filament\Actions;
use App\Filament\Widgets\ramadhanwidgets;
use Filament\Resources\Pages\ListRecords;

class ListLaporanramadhantims extends ListRecords
{
    protected static string $resource = LaporanramadhantimResource::class;

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
