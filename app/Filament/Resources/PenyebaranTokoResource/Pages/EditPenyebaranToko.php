<?php

namespace App\Filament\Resources\PenyebaranTokoResource\Pages;

use App\Filament\Resources\PenyebaranTokoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPenyebaranToko extends EditRecord
{
    protected static string $resource = PenyebaranTokoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
