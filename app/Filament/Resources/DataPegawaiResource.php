<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataPegawaiResource\Pages;
use App\Filament\Resources\DataPegawaiResource\RelationManagers;
use App\Models\DataPegawai;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DataPegawaiResource extends Resource
{
    protected static ?string $model = DataPegawai::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'DATA NAMA PEGAWAI';
    protected static ?string $pluralLabel = 'Data nama pegawai';
    protected static ?string $modelLabel = 'Data nama pegawai';

    protected static ?string $navigationGroup = 'DATA PEGAWAI'; // ✅ Ini menambah group menu

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_pegawai')
                    ->required()
                    ->maxLength(100)
                    ->label('Nama Pegawai'),

                Forms\Components\TextInput::make('tempat_lahir')
                    ->maxLength(100)
                    ->label('Tempat Lahir'),

                Forms\Components\DatePicker::make('tanggal_lahir')
                    ->label('Tanggal Lahir'),

                Forms\Components\Select::make('jenis_kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan'
                    ])
                    ->required()
                    ->label('Jenis Kelamin'),

                Forms\Components\Textarea::make('alamat')
                    ->columnSpanFull()
                    ->label('Alamat'),

                Forms\Components\TextInput::make('no_telepon')
                    ->maxLength(15)
                    ->label('No. Telepon'),

                Forms\Components\TextInput::make('id_jabatan')
                    ->numeric()
                    ->nullable()
                    ->label('ID Jabatan'),

                Forms\Components\DatePicker::make('tanggal_masuk')
                    ->required()
                    ->label('Tanggal Masuk'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
             ->columns([
                Tables\Columns\TextColumn::make('nama_pegawai')
                    ->searchable()
                    ->label('Nama Pegawai'),

                Tables\Columns\TextColumn::make('tempat_lahir')
                    ->label('Tempat Lahir'),

                Tables\Columns\TextColumn::make('tanggal_lahir')
                    ->date()
                    ->label('Tanggal Lahir'),

                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->formatStateUsing(fn (string $state): string => $state === 'L' ? 'Laki-laki' : 'Perempuan')
                    ->label('Jenis Kelamin'),

                Tables\Columns\TextColumn::make('no_telepon')
                    ->label('No. Telepon'),

                Tables\Columns\TextColumn::make('id_jabatan')
                    ->label('ID Jabatan'),

                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->date()
                    ->label('Tanggal Masuk'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Dibuat Pada'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Diubah Pada'),
             ])
            ->filters([
                //
            ])
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataPegawais::route('/'),
            'create' => Pages\CreateDataPegawai::route('/create'),
            'edit' => Pages\EditDataPegawai::route('/{record}/edit'),
        ];
    }
}
