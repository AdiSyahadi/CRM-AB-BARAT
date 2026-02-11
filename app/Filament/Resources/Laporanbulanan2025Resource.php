<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Laporanbulanan2025Resource\Pages;
use App\Filament\Resources\Laporanbulanan2025Resource\RelationManagers;
use App\Models\LaporanPerolehan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class Laporanbulanan2025Resource extends Resource
{
    protected static ?string $model = LaporanPerolehan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'PEROLEHAN BULANAN 2025';
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
        ->recordUrl(null) // 🔒 Menonaktifkan clickable row
        ->query(
            LaporanPerolehan::query()
            ->selectRaw('
                MIN(id) AS id, 
                nama_cs,
                SUM(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 1, jml_perolehan, 0)) AS Januari,
                SUM(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 2, jml_perolehan, 0)) AS Februari,
                SUM(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 3, jml_perolehan, 0)) AS Maret,
                SUM(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 4, jml_perolehan, 0)) AS April,
                SUM(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 5, jml_perolehan, 0)) AS Mei,
                SUM(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 6, jml_perolehan, 0)) AS Juni,
                SUM(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 7, jml_perolehan, 0)) AS Juli,
                SUM(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 8, jml_perolehan, 0)) AS Agustus,
                SUM(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 9, jml_perolehan, 0)) AS September,
                SUM(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 10, jml_perolehan, 0)) AS Oktober,
                SUM(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 11, jml_perolehan, 0)) AS November,
                SUM(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 12, jml_perolehan, 0)) AS Desember,
                SUM(IF(YEAR(tanggal) = 2025, jml_perolehan, 0)) AS total_perolehan,
                COUNT(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 1 AND jml_perolehan > 0, jml_perolehan, NULL)) AS Donatur_Berdonasi_Januari,
                SUM(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 1) AS Transaksi_Januari,
                COUNT(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 2 AND jml_perolehan > 0, jml_perolehan, NULL)) AS Donatur_Berdonasi_Februari,
                SUM(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 2) AS Transaksi_Februari,
                COUNT(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 3 AND jml_perolehan > 0, jml_perolehan, NULL)) AS Donatur_Berdonasi_Maret,
                SUM(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 3) AS Transaksi_Maret,
                COUNT(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 4 AND jml_perolehan > 0, jml_perolehan, NULL)) AS Donatur_Berdonasi_April,
                SUM(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 4) AS Transaksi_April,
                COUNT(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 5 AND jml_perolehan > 0, jml_perolehan, NULL)) AS Donatur_Berdonasi_Mei,
                SUM(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 5) AS Transaksi_Mei,
                COUNT(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 6 AND jml_perolehan > 0, jml_perolehan, NULL)) AS Donatur_Berdonasi_Juni,
                SUM(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 6) AS Transaksi_Juni,
                COUNT(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 7 AND jml_perolehan > 0, jml_perolehan, NULL)) AS Donatur_Berdonasi_Juli,
                SUM(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 7) AS Transaksi_Juli,
                COUNT(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 8 AND jml_perolehan > 0, jml_perolehan, NULL)) AS Donatur_Berdonasi_Agustus,
                SUM(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 8) AS Transaksi_Agustus,
                COUNT(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 9 AND jml_perolehan > 0, jml_perolehan, NULL)) AS Donatur_Berdonasi_September,
                SUM(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 9) AS Transaksi_September,
                COUNT(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 10 AND jml_perolehan > 0, jml_perolehan, NULL)) AS Donatur_Berdonasi_Oktober,
                SUM(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 10) AS Transaksi_Oktober,
                COUNT(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 11 AND jml_perolehan > 0, jml_perolehan, NULL)) AS Donatur_Berdonasi_November,
                SUM(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 11) AS Transaksi_November,
                COUNT(IF(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 12 AND jml_perolehan > 0, jml_perolehan, NULL)) AS Donatur_Berdonasi_Desember,
                SUM(YEAR(tanggal) = 2025 AND MONTH(tanggal) = 12) AS Transaksi_Desember
            ')
            ->whereYear('tanggal', 2025)
            ->groupBy('nama_cs')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama_cs')->label('Nama CS')->searchable(),
                // Kolom untuk bulan dan total perolehan
                Tables\Columns\TextColumn::make('Januari')->label('Januari')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('Donatur_Berdonasi_Januari')->label('Donatur Berdonasi'),
                Tables\Columns\TextColumn::make('Transaksi_Januari')->label('Transaksi Januari'),
                Tables\Columns\TextColumn::make('Februari')->label('Februari')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('Donatur_Berdonasi_Februari')->label('Donatur Berdonasi'),
                Tables\Columns\TextColumn::make('Transaksi_Februari')->label('Transaksi Februari'),
                Tables\Columns\TextColumn::make('Maret')->label('Maret')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('Donatur_Berdonasi_Maret')->label('Donatur Berdonasi'),
                Tables\Columns\TextColumn::make('Transaksi_Maret')->label('Transaksi Maret'),
                Tables\Columns\TextColumn::make('April')->label('April')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('Donatur_Berdonasi_April')->label('Donatur Berdonasi'),
                Tables\Columns\TextColumn::make('Transaksi_April')->label('Transaksi April'),
                Tables\Columns\TextColumn::make('Mei')->label('Mei')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('Donatur_Berdonasi_Mei')->label('Donatur Berdonasi'),
                Tables\Columns\TextColumn::make('Transaksi_Mei')->label('Transaksi Mei'),
                Tables\Columns\TextColumn::make('Juni')->label('Juni')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('Donatur_Berdonasi_Juni')->label('Donatur Berdonasi'),
                Tables\Columns\TextColumn::make('Transaksi_Juni')->label('Transaksi Juni'),
                Tables\Columns\TextColumn::make('Juli')->label('Juli')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('Donatur_Berdonasi_Juli')->label('Donatur Berdonasi'),
                Tables\Columns\TextColumn::make('Transaksi_Juli')->label('Transaksi Juli'),
                Tables\Columns\TextColumn::make('Agustus')->label('Agustus')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('Donatur_Berdonasi_Agustus')->label('Donatur Berdonasi'),
                Tables\Columns\TextColumn::make('Transaksi_Agustus')->label('Transaksi Agustus'),
                Tables\Columns\TextColumn::make('September')->label('September')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('Donatur_Berdonasi_September')->label('Donatur Berdonasi'),
                Tables\Columns\TextColumn::make('Transaksi_September')->label('Transaksi September'),
                Tables\Columns\TextColumn::make('Oktober')->label('Oktober')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('Donatur_Berdonasi_Oktober')->label('Donatur Berdonasi'),
                Tables\Columns\TextColumn::make('Transaksi_Oktober')->label('Transaksi Oktober'),
                Tables\Columns\TextColumn::make('November')->label('November')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('Donatur_Berdonasi_November')->label('Donatur Berdonasi'),
                Tables\Columns\TextColumn::make('Transaksi_November')->label('Transaksi November'),
                Tables\Columns\TextColumn::make('Desember')->label('Desember')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('Donatur_Berdonasi_Desember')->label('Donatur Berdonasi'),
                Tables\Columns\TextColumn::make('Transaksi_Desember')->label('Transaksi Desember'),
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
            'index' => Pages\ListLaporanbulanan2025s::route('/'),
            'create' => Pages\CreateLaporanbulanan2025::route('/create'),
            'edit' => Pages\EditLaporanbulanan2025::route('/{record}/edit'),
        ];
    }
}
