<?php

namespace App\Filament\Resources\PartershipResource\Pages;

use App\Filament\Resources\PartershipResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPartership extends EditRecord
{
    protected static string $resource = PartershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
