<?php

namespace App\Filament\Resources\CustomerServiceResource\Pages;

use App\Filament\Resources\CustomerServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomerServices extends ListRecords
{
    protected static string $resource = CustomerServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
             ->label('Buat Akun Baru') // ← Ganti label di sini
               ->icon('heroicon-m-plus'),    // ← Tambahkan ikon jika ingin
        ];
    }
}
