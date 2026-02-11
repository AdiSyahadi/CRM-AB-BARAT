<?php

namespace App\Filament\Resources\Laporanbulanan2025Resource\Pages;

use App\Filament\Resources\Laporanbulanan2025Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaporanbulanan2025 extends EditRecord
{
    protected static string $resource = Laporanbulanan2025Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
