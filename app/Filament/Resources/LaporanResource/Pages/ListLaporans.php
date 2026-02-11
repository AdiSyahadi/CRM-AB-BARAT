<?php

namespace App\Filament\Resources\LaporanResource\Pages;

use App\Filament\Resources\LaporanResource;
use Filament\Actions;
use App\Filament\Widgets\lapharwid;
use App\Filament\Widgets\lapharwid2;
use Filament\Resources\Pages\ListRecords;

class ListLaporans extends ListRecords
{
    protected static string $resource = LaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            lapharwid::class,
            lapharwid2::class,
        ];
    }
}
