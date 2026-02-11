<?php
namespace App\Filament\Resources;

use App\Filament\Resources\Kwitansiv2Resource\Pages;
use App\Models\Kwitansiv2;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;

class Kwitansiv2Resource extends Resource
{
    protected static ?string $model = Kwitansiv2::class;
    protected static ?string $navigationGroup = 'KWITANSI';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Kwitansi Versi 2'; // Mengubah nama di sidebar

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section untuk Data Donatur
                Section::make('Data Donatur')
                    ->schema([
                        Forms\Components\Grid::make(2) // Membuat layout dengan 2 kolom
                        ->schema([
                        //Forms\Components\TextInput::make('nomor_kwitansi')->required()->unique(),
                        Forms\Components\DatePicker::make('tanggal')->required()->label('Tanggal'),
                        Forms\Components\TextInput::make('nama_donatur')->required(),
                        Forms\Components\TextInput::make('telepon')->required(),
                        Forms\Components\TextInput::make('alamat')->required(),
                        
                        ]),
                       
                    ]),

                // Section untuk Data Donasi
                Section::make('Data Donasi')
                ->schema([
                    Forms\Components\Grid::make(2) // Membuat layout dengan 2 kolom
                        ->schema([
                            Forms\Components\TextInput::make('nama_donasi')->required()->label('Nama Program'),
                            // Jumlah Donasi 1
                            Forms\Components\TextInput::make('jumlah_donasi')
                                ->required()
                                ->numeric()
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',')
                                ->label('Nominal')
                                ->reactive()
                                ->debounce(500) // Menunggu 500ms setelah input terakhir
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    self::updateTotalDonasi($get, $set);
                                }),
            
                            Forms\Components\TextInput::make('nama_donasi2')->label('Nama Program 2'),
                            
                            // Jumlah Donasi 2
                            Forms\Components\TextInput::make('jumlah_donasi2')
                                ->numeric()
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',')
                                ->label('Nominal 2')
                                ->reactive()
                                ->debounce(500) // Menunggu 500ms setelah input terakhir
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    self::updateTotalDonasi($get, $set);
                                }),
            
                            Forms\Components\TextInput::make('nama_donasi3')->label('Nama Program 3'),
                            
                            // Jumlah Donasi 3
                            Forms\Components\TextInput::make('jumlah_donasi3')
                                ->numeric()
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',')
                                ->label('Nominal 3')
                                ->reactive()
                                ->debounce(500) // Menunggu 500ms setelah input terakhir
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    self::updateTotalDonasi($get, $set);
                                }),
            
                            Forms\Components\TextInput::make('nama_donasi4')->label('Nama Program 4'),
                            
                            // Jumlah Donasi 4
                            Forms\Components\TextInput::make('jumlah_donasi4')
                                ->numeric()
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',')
                                ->label('Nominal 4')
                                ->reactive()
                                ->debounce(500) // Menunggu 500ms setelah input terakhir
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    self::updateTotalDonasi($get, $set);
                                }),
            
                            Forms\Components\TextInput::make('nama_donasi5')->label('Nama Program 5'),
                            
                            // Jumlah Donasi 5
                            Forms\Components\TextInput::make('jumlah_donasi5')
                                ->numeric()
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',')
                                ->label('Nominal 5')
                                ->reactive()
                                ->debounce(500) // Menunggu 500ms setelah input terakhir
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    self::updateTotalDonasi($get, $set);
                                }),
                        ]),
                ]),            

                // Section untuk Data Lainnya
                Section::make('Data Lainnya')
                    ->schema([
                        Forms\Components\Grid::make(2) // Membuat layout dengan 2 kolom
                        ->schema([
                            Forms\Components\TextInput::make('diserahkan')->required(),
                            Forms\Components\TextInput::make('diterima')->required(),
                            
                            // Total Donasi yang dihitung otomatis
                            Forms\Components\TextInput::make('total_donasi')
                                ->label('Total Donasi')
                                ->numeric()  // Memastikan hanya angka yang bisa dimasukkan
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ','),  // Masking dengan format Rupiah
    
                            Forms\Components\TextInput::make('terbilang')->required(),
                        ])
                    ]),
            ]);
    }

    public static function table(Table $table): Table
   

    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('nomor_kwitansi')->sortable(),
                Tables\Columns\TextColumn::make('nama_donatur')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('tanggal')->sortable(),
                Tables\Columns\TextColumn::make('alamat')->sortable(),
                Tables\Columns\TextColumn::make('telepon')->sortable(),
                Tables\Columns\TextColumn::make('nama_donasi')->sortable(),
                Tables\Columns\TextColumn::make('diserahkan')->sortable(),
                Tables\Columns\TextColumn::make('diterima')->sortable(),
                Tables\Columns\TextColumn::make('total_donasi')
                    ->sortable()
                    ->currency('IDR')  // Format Rupiah
                    ->formatStateUsing(fn (string $state): string => 'Rp ' . number_format($state, 0, ',', '.')),
            ])
            ->defaultSort('tanggal', 'desc') // Urutkan berdasarkan tanggal terbaru secara default
            ->filters([
                Tables\Filters\Filter::make('Hari Ini')
                    ->query(fn (Builder $query) => $query->whereDate('tanggal', now()->toDateString())), // Menggunakan Illuminate\Database\Eloquent\Builder
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download_pdf')
                    ->label('Download PDF')
                    ->url(fn (Kwitansiv2 $record) => route('kwitansiv2.pdf', $record->id)),
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
            'index' => Pages\ListKwitansiv2s::route('/'),
            'create' => Pages\CreateKwitansiv2::route('/create'),
            'edit' => Pages\EditKwitansiv2::route('/{record}/edit'),
        ];
    }

    public static function updateTotalDonasi(callable $get, callable $set)
    {
        $total = (
            intval(str_replace(['Rp', '.', ','], '', $get('jumlah_donasi') ?? 0)) +
            intval(str_replace(['Rp', '.', ','], '', $get('jumlah_donasi2') ?? 0)) +
            intval(str_replace(['Rp', '.', ','], '', $get('jumlah_donasi3') ?? 0)) +
            intval(str_replace(['Rp', '.', ','], '', $get('jumlah_donasi4') ?? 0)) +
            intval(str_replace(['Rp', '.', ','], '', $get('jumlah_donasi5') ?? 0))
        );

        $set('total_donasi', $total);
    }
}
