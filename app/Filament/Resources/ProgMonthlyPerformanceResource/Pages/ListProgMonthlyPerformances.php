<?php

namespace App\Filament\Resources\ProgMonthlyPerformanceResource\Pages;

use App\Filament\Resources\ProgMonthlyPerformanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProgMonthlyPerformances extends ListRecords
{
    protected static string $resource = ProgMonthlyPerformanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
