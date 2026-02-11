<?php

namespace App\Filament\Resources\AbsensHResource\Pages;

use App\Filament\Resources\AbsensHResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAbsensHS extends ListRecords
{
    protected static string $resource = AbsensHResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
