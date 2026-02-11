<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanResource\Pages;
use App\Filament\Resources\LaporanResource\RelationManagers;
use App\Models\LaporanPerolehan;
use Filament\Forms;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LaporanResource extends Resource
{
    protected static ?string $model = LaporanPerolehan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'PEROLEHAN HARIAN';
    protected static ?string $navigationGroup = 'DATA LAPORAN CS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->query(function () {
            return LaporanPerolehan::query()
                ->selectRaw('
                    MIN(id) as id, -- ID unik untuk mencegah error routing
                    nama_cs,
                    tim,
                    tanggal,
                    COUNT(*) as jumlah_laporan, -- Menghitung jumlah laporan yang diinput
                    SUM(jml_database) as total_database,
                    SUM(jml_perolehan) as total_perolehan,
                    SUM(CASE WHEN prg_cross_selling IS NOT NULL THEN jml_perolehan ELSE 0 END) as total_cross_selling
                ')
                ->groupBy(['nama_cs', 'tim', 'tanggal']); // Grupkan berdasarkan kolom ini
        })
        ->columns([
            TextColumn::make('tanggal')
                ->label('Tanggal')
                ->sortable(),
            TextColumn::make('tim')
                ->label('Tim')
                ->sortable(),
            TextColumn::make('nama_cs')
                ->label('Nama CS')
                ->sortable()
                ->searchable(),
            TextColumn::make('jumlah_laporan') // Kolom baru untuk jumlah laporan
                ->label('Jumlah Laporan')
                ->sortable(),
            TextColumn::make('total_database')
                ->label('Total Database')
                ->sortable(),
            TextColumn::make('total_perolehan')
                ->label('Total Perolehan')
                ->sortable(),
            TextColumn::make('total_cross_selling')
                ->label('Total Cross Selling')
                ->sortable(),
        ])
        ->filters([
            Tables\Filters\Filter::make('Hari Ini')
                ->form([
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->default(now()->toDateString()),
                    Forms\Components\DatePicker::make('end_date')
                        ->label('Tanggal Akhir')
                        ->default(now()->toDateString()),
                ])
                ->query(function (Builder $query, array $data) {
                    if (!empty($data['start_date'])) {
                        $query->whereDate('tanggal', '>=', $data['start_date']);
                    }
                    if (!empty($data['end_date'])) {
                        $query->whereDate('tanggal', '<=', $data['end_date']);
                    }
                    return $query;
                }),
        ])
        ->poll('10s')
        ->actions([
            Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListLaporans::route('/'),
            'create' => Pages\CreateLaporan::route('/create'),
            'edit' => Pages\EditLaporan::route('/{record}/edit'),
        ];
    }
}
