<?php

namespace App\Filament\Resources\LaporanPerolehanResource\Pages;

use App\Filament\Resources\LaporanPerolehanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaporanPerolehans extends ListRecords
{
    protected static string $resource = LaporanPerolehanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
