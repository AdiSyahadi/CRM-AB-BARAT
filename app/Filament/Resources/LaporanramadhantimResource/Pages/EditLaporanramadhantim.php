<?php

namespace App\Filament\Resources\LaporanramadhantimResource\Pages;

use App\Filament\Resources\LaporanramadhantimResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaporanramadhantim extends EditRecord
{
    protected static string $resource = LaporanramadhantimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
