<?php

namespace App\Filament\Resources\Kwitansiv2Resource\Pages;

use App\Filament\Resources\Kwitansiv2Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKwitansiv2 extends EditRecord
{
    protected static string $resource = Kwitansiv2Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
