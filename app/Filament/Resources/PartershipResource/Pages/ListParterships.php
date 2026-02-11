<?php

namespace App\Filament\Resources\PartershipResource\Pages;

use App\Filament\Resources\PartershipResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParterships extends ListRecords
{
    protected static string $resource = PartershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
