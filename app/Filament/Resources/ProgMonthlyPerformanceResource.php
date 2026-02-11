<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgMonthlyPerformanceResource\Pages;
use App\Filament\Resources\ProgMonthlyPerformanceResource\RelationManagers;
use App\Models\ProgMonthlyPerformance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\LaporanPerolehan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProgMonthlyPerformanceResource extends Resource
{
    protected static ?string $model = ProgMonthlyPerformance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'PEROLEHAN BULANAN TIM';
    protected static ?string $navigationGroup = 'DATA LAPORAN TIM';

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
        ->query(
            LaporanPerolehan::query()
                ->selectRaw('MIN(id) as id, -- Pastikan "id" disertakan
                    tim, 
                    SUM(IF(MONTH(tanggal) = 1, jml_perolehan, 0)) AS Januari,
                    SUM(IF(MONTH(tanggal) = 2, jml_perolehan, 0)) AS Februari,
                    SUM(IF(MONTH(tanggal) = 3, jml_perolehan, 0)) AS Maret,
                    SUM(IF(MONTH(tanggal) = 4, jml_perolehan, 0)) AS April,
                    SUM(IF(MONTH(tanggal) = 5, jml_perolehan, 0)) AS Mei,
                    SUM(IF(MONTH(tanggal) = 6, jml_perolehan, 0)) AS Juni,
                    SUM(IF(MONTH(tanggal) = 7, jml_perolehan, 0)) AS Juli,
                    SUM(IF(MONTH(tanggal) = 8, jml_perolehan, 0)) AS Agustus,
                    SUM(IF(MONTH(tanggal) = 9, jml_perolehan, 0)) AS September,
                    SUM(IF(MONTH(tanggal) = 10, jml_perolehan, 0)) AS Oktober,
                    SUM(IF(MONTH(tanggal) = 11, jml_perolehan, 0)) AS November,
                    SUM(IF(MONTH(tanggal) = 12, jml_perolehan, 0)) AS Desember,
                    SUM(jml_perolehan) as total_perolehan')
                ->groupBy('tim')
        )
        ->columns([
            Tables\Columns\TextColumn::make('tim')->label('Nama CS')->searchable(),
            Tables\Columns\TextColumn::make('Januari')->label('Januari')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            Tables\Columns\TextColumn::make('Februari')->label('Februari')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            Tables\Columns\TextColumn::make('Maret')->label('Maret')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            Tables\Columns\TextColumn::make('April')->label('April')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            Tables\Columns\TextColumn::make('Mei')->label('Mei')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            Tables\Columns\TextColumn::make('Juni')->label('Juni')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            Tables\Columns\TextColumn::make('Juli')->label('Juli')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            Tables\Columns\TextColumn::make('Agustus')->label('Agustus')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            Tables\Columns\TextColumn::make('September')->label('September')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            Tables\Columns\TextColumn::make('Oktober')->label('Oktober')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            Tables\Columns\TextColumn::make('November')->label('November')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            Tables\Columns\TextColumn::make('Desember')->label('Desember')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            Tables\Columns\TextColumn::make('total_perolehan')->label('Total Perolehan')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
        ])
            ->filters([
                //
            ])
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
            'index' => Pages\ListProgMonthlyPerformances::route('/'),
            'create' => Pages\CreateProgMonthlyPerformance::route('/create'),
            'edit' => Pages\EditProgMonthlyPerformance::route('/{record}/edit'),
        ];
    }
}
