<?php

namespace App\Filament\Resources\LaporanPerolehanResource\Pages;

use App\Filament\Resources\LaporanPerolehanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaporanPerolehan extends EditRecord
{
    protected static string $resource = LaporanPerolehanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
