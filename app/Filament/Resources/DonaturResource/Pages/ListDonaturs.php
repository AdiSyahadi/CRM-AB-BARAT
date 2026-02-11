<?php

namespace App\Filament\Resources\DonaturResource\Pages;

use App\Filament\Resources\DonaturResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDonaturs extends ListRecords
{
    protected static string $resource = DonaturResource::class;

    public function mount(): void
    {
        // Redirect ke Donatur CRM yang baru
        header('Location: ' . route('donatur.index'));
        exit;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
