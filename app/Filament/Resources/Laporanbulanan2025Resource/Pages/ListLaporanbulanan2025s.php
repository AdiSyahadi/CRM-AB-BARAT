<?php

namespace App\Filament\Resources\Laporanbulanan2025Resource\Pages;

use App\Filament\Resources\Laporanbulanan2025Resource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaporanbulanan2025s extends ListRecords
{
    protected static string $resource = Laporanbulanan2025Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
