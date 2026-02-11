<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerServiceResource\Pages;
use App\Models\CustomerService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerServiceResource extends Resource
{
    protected static ?string $model = CustomerService::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Customer Service';

    protected static ?string $navigationGroup = 'DATA NAMA CS'; // ✅ Ini menambah group menu

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama CS')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('team')
                ->label('Tim')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama CS')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('team')
                    ->label('Tim')
                    ->sortable(),
            ])
            ->filters([])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerServices::route('/'),
            'create' => Pages\CreateCustomerService::route('/create'),
            'edit' => Pages\EditCustomerService::route('/{record}/edit'),
        ];
    }
}
