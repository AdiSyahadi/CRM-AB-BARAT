<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsensiResource\Pages;
use App\Models\Absensi;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker as FilterDatePicker;

class AbsensiResource extends Resource
{
    protected static ?string $model = Absensi::class;

    protected static ?string $navigationGroup = 'Data Absensi';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Absensi ubudiyah';
    protected static ?string $pluralLabel = 'Daftar absen ubudiyah'; // Judul utama di halaman
    protected static ?string $modelLabel = 'Daftar absen ubudiyah';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Absensi')
                ->schema([
                    TextInput::make('nama')->required(),
                    TimePicker::make('jam')->required(),
                    DatePicker::make('tanggal')->required(),
                    TextInput::make('status')->required(),
                    TextInput::make('ubudiyah')->required(),
                    Textarea::make('keterangan'),
                    FileUpload::make('foto')
                        ->directory('foto')
                        ->image()
                        ->imagePreviewHeight('100')
                        ->maxSize(1024),
                    TextInput::make('alamat')->columnSpanFull(),
                    TextInput::make('latitude'),
                    TextInput::make('longitude'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null) // 🔒 Menonaktifkan clickable row
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('nama')->searchable(),
                TextColumn::make('jam')
                    ->label('Jam (WIB)')
                    ->formatStateUsing(function ($state) {
                        return \Carbon\Carbon::parse($state, 'UTC')
                            ->setTimezone('Asia/Jakarta')
                            ->format('H:i');
                    }),
                TextColumn::make('tanggal')->date(),
                TextColumn::make('status'),
                TextColumn::make('ubudiyah'),
                TextColumn::make('keterangan')->limit(30),
                Tables\Columns\TextColumn::make('foto')
                ->label('Link Foto')
                ->url(fn ($record) => "https://abbarat.abdashboard.com/{$record->foto}")
                ->openUrlInNewTab()
                ->formatStateUsing(fn ($state) => 'Klik untuk lihat foto'),
                TextColumn::make('alamat')->limit(50),
                TextColumn::make('latitude'),
                TextColumn::make('longitude'),
            ])
            ->filters([
                Filter::make('Rentang Tanggal')
                    ->form([
                        FilterDatePicker::make('from')->label('Dari Tanggal'),
                        FilterDatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('tanggal', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('tanggal', '<=', $data['until']));
                    }),
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
            'index' => Pages\ListAbsensis::route('/'),
            'create' => Pages\CreateAbsensi::route('/create'),
            'edit' => Pages\EditAbsensi::route('/{record}/edit'),
        ];
    }
}
