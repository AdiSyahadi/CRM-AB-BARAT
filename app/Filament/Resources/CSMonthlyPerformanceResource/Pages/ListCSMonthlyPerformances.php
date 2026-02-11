<?php

namespace App\Filament\Resources\CSMonthlyPerformanceResource\Pages;

use App\Filament\Resources\CSMonthlyPerformanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCSMonthlyPerformances extends ListRecords
{
    protected static string $resource = CSMonthlyPerformanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
