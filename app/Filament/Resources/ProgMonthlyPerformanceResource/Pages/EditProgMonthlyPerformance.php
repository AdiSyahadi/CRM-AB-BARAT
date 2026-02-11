<?php

namespace App\Filament\Resources\ProgMonthlyPerformanceResource\Pages;

use App\Filament\Resources\ProgMonthlyPerformanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProgMonthlyPerformance extends EditRecord
{
    protected static string $resource = ProgMonthlyPerformanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
