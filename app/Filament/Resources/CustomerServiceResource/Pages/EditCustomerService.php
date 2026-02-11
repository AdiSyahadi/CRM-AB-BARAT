<?php

namespace App\Filament\Resources\CustomerServiceResource\Pages;

use App\Filament\Resources\CustomerServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerService extends EditRecord
{
    protected static string $resource = CustomerServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
