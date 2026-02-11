<?php

namespace App\Filament\Resources\AbsensHResource\Pages;

use App\Filament\Resources\AbsensHResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAbsensH extends EditRecord
{
    protected static string $resource = AbsensHResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
