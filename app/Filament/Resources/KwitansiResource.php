<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KwitansiResource\Pages;
use App\Models\Kwitansi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;

class KwitansiResource extends Resource
{
    protected static ?string $model = Kwitansi::class;
    protected static ?string $navigationGroup = 'KWITANSI';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Kwitansi Versi 1'; // Mengubah nama di sidebar

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // Section untuk data bagian 1
                Forms\Components\Section::make('Data ')
                    ->schema([
                        Forms\Components\Grid::make(2) // Membuat layout dengan 3 kolom
                        ->schema([
                        
                        Forms\Components\DatePicker::make('tanggal')
                            ->required()
                            ->label('Tanggal')
                            ->default(now()),
                        Forms\Components\TextInput::make('nama_donatur')
                            ->required()
                            ->label('Nama Donatur'),
                            //Forms\Components\TextInput::make('nomor_kwitansi')
                            //->required()
                            //->unique(), // Pastikan nomor kwitansi unik
                        ])
                    ]),

                // Section untuk data bagian 2
                Forms\Components\Section::make('Data Donasi')
                    ->schema([
                        Forms\Components\Grid::make(2) // Membuat layout dengan 3 kolom
                            ->schema([
                                Forms\Components\TextInput::make('jumlah_donasi')
                                    ->required()
                                    ->numeric()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',')
                                    ->label('Jumlah Donasi'),
                                Forms\Components\TextInput::make('nama_donasi')
                                    ->required()
                                    ->label('Nama Program'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null) // 🔒 Menonaktifkan clickable row
            ->columns([
                //Tables\Columns\TextColumn::make('id')->sortable(),
                //Tables\Columns\TextColumn::make('nomor_kwitansi')->sortable(),
                Tables\Columns\TextColumn::make('nama_donatur')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('tanggal')->sortable(),
                Tables\Columns\TextColumn::make('jumlah_donasi')->sortable(),
                Tables\Columns\TextColumn::make('nama_donasi')->sortable()->label('Nama Program')->searchable(),
            ])
            ->defaultSort('tanggal', 'desc') // Urutkan berdasarkan tanggal terbaru secara default
            ->filters([
                Tables\Filters\Filter::make('Tampilkan Data Hari Ini')
                    ->query(fn (Builder $query) => $query->whereDate('tanggal', now()->toDateString())), // Menggunakan Illuminate\Database\Eloquent\Builder
            ])
            ->actions([
                Tables\Actions\EditAction::make(),  // Aksi Edit
                //Tables\Actions\DeleteAction::make(),  // Aksi Delete
                Tables\Actions\Action::make('download_pdf')  // Aksi Download PDF
                    ->label('Print')
                    ->url(fn (Kwitansi $record) => route('kwitansi.pdf', $record->id)),
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
            'index' => Pages\ListKwitansis::route('/'),
            'create' => Pages\CreateKwitansi::route('/create'),
            'edit' => Pages\EditKwitansi::route('/{record}/edit'),
        ];
    }
}
