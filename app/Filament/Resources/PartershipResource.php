<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartershipResource\Pages;
use App\Models\LaporanPerolehan;
use App\Models\CustomerService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns;

class PartershipResource extends Resource
{
    protected static ?string $model = LaporanPerolehan::class;
    protected static ?string $navigationLabel = 'REALTIME PARTNERSHIP';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                    Forms\Components\DatePicker::make('tanggal')
                        ->label('Tanggal')
                        ->required()
                        ->default(now()),
                    Forms\Components\Select::make('nama_cs')
                        ->label('Nama CS')
                        ->options(CustomerService::query()->pluck('name', 'name'))
                        ->required()
                        ->searchable(),
                    Forms\Components\Select::make('tim')
                        ->label('Tim')
                        ->options(CustomerService::query()->pluck('team', 'team'))
                        ->required()
                        ->searchable(),
                Forms\Components\Select::make('hasil_dari')
                    ->label('Hasil Dari')
                    ->options([
                        'Cross Selling' => 'Cross Selling',
                        'Infaq' => 'Infaq',
                        'Wakaf' => 'Wakaf',
                        'Produk' => 'Produk',
                        'Zakat' => 'Zakat',
                        'Platform' => 'Platform',
                        'Partnership' => 'Partnership',])
                        ->searchable(),
                Forms\Components\TextInput::make('nama_donatur')
                    ->label('Nama Mitra')
                    ->required(),
                Forms\Components\Select::make('nama_bank')
                    ->label('Nama Bank')
                    ->options([
                        'Bank BRI' => 'Bank BRI',
                        'Bank Mandiri' => 'Bank Mandiri',
                        'Bank BNI' => 'Bank BNI',
                        'Bank BTN' => 'Bank BTN',
                        'Bank BCA' => 'Bank BCA',
                        'Bank CIMB Niaga' => 'Bank CIMB Niaga',
                        'Bank Maybank Indonesia' => 'Bank Maybank Indonesia',
                        'Bank Permata' => 'Bank Permata',
                        'Bank OCBC NISP' => 'Bank OCBC NISP',
                        'Bank Danamon' => 'Bank Danamon',
                        'Bank BTPN' => 'Bank BTPN',
                        'Bank Syariah Indonesia' => 'Bank Syariah Indonesia',
                        'Bank Jago' => 'Bank Jago',
                        'Bank Mega' => 'Bank Mega',
                        'Bank Sinarmas' => 'Bank Sinarmas',
                        'Bank Muamalat' => 'Bank Muamalat',
                        'Bank Commonwealth' => 'Bank Commonwealth',
                        'Bank Artha Graha Internasional' => 'Bank Artha Graha Internasional',
                        'Bank Mayapada' => 'Bank Mayapada',
                        'Bank KB Bukopin' => 'Bank KB Bukopin',
                        'Bank BJB' => 'Bank BJB',
                        'Bank DKI' => 'Bank DKI',
                        'Bank Jatim' => 'Bank Jatim',
                        'Bank Jateng' => 'Bank Jateng',
                        'Bank Sumut' => 'Bank Sumut',
                        'Bank Aceh Syariah' => 'Bank Aceh Syariah',
                        'Bank Nagari' => 'Bank Nagari',
                        'Bank Riau Kepri' => 'Bank Riau Kepri',
                        'Bank Sumsel Babel' => 'Bank Sumsel Babel',
                        'Bank Lampung' => 'Bank Lampung',
                        'Bank Kalsel' => 'Bank Kalsel',
                        'Bank Kaltimtara' => 'Bank Kaltimtara',
                        'Bank Kalbar' => 'Bank Kalbar',
                        'Bank Sulselbar' => 'Bank Sulselbar',
                        'Bank SulutGo' => 'Bank SulutGo',
                        'Bank NTB Syariah' => 'Bank NTB Syariah',
                        'Bank NTT' => 'Bank NTT',
                        'Bank Maluku Malut' => 'Bank Maluku Malut',
                        'Bank Papua' => 'Bank Papua',
                        'Bank Bengkulu' => 'Bank Bengkulu',
                        'Bank Sulteng' => 'Bank Sulteng',
                    ])
                    ->searchable(),
                Forms\Components\TextInput::make('no_rek')
                    ->label('Nomor Rekening'),
                Forms\Components\TextInput::make('no_hp')
                    ->label('No HP')
                    ->tel(),
                    //->required(),
                Forms\Components\TextInput::make('jml_perolehan')
                    ->label('Nominal')
                    ->numeric()
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ','),
                Forms\Components\TextInput::make('keterangan')
                    ->label('Keterangan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null) // 🔒 Menonaktifkan clickable row
            ->columns([
                Columns\TextColumn::make('tanggal')->label('Tanggal')->sortable(),
                Columns\TextColumn::make('nama_cs')->label('Nama CS')->sortable(),
                Columns\TextColumn::make('tim')->label('Tim')->sortable(),
                Columns\TextColumn::make('hasil_dari')->label('Kategori')->sortable(),
                Columns\TextColumn::make('nama_donatur')->label('Nama Mitra')->sortable(),
                Columns\TextColumn::make('no_hp')->label('No HP')->sortable(),
                Columns\TextColumn::make('jml_perolehan')->label('Nominal')->sortable()->money('IDR'),
                Columns\TextColumn::make('no_rek')->label('Nomer Rekening')->sortable(),
                Columns\TextColumn::make('nama_bank')->label('Nama Bank')->sortable(),
                Columns\TextColumn::make('keterangan')->label('Keterangan')->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('partnership_only')
                ->label('Hanya Partnership')
                ->query(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('hasil_dari', 'Partnership'))
                ->default(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParterships::route('/'),
            'create' => Pages\CreatePartership::route('/create'),
            'edit' => Pages\EditPartership::route('/{record}/edit'),
        ];
    }
}
