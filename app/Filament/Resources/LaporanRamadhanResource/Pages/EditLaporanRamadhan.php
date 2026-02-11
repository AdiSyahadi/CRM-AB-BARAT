<?php

namespace App\Filament\Resources\LaporanRamadhanResource\Pages;

use App\Filament\Resources\LaporanRamadhanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaporanRamadhan extends EditRecord
{
    protected static string $resource = LaporanRamadhanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
