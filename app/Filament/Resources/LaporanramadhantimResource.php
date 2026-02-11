<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanramadhantimResource\Pages;
use App\Models\LaporanPerolehan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class LaporanramadhantimResource extends Resource
{
    protected static ?string $model = LaporanPerolehan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Ramadhan';
    protected static ?string $navigationLabel = 'Laporan Ramadhan TIM';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        $tanggal_awal_2024 = '2024-03-12';
        $tanggal_akhir_2024 = date('Y-m-d', strtotime("$tanggal_awal_2024 +30 days")); // hari ke-30 Ramadhan
        
        $tanggal_awal_2025 = '2025-03-01';
        $tanggal_akhir_2025 = date('Y-m-d', strtotime("$tanggal_awal_2025 +30 days")); // hari ke-30 Ramadhan

        return $table
            ->query(
            LaporanPerolehan::query()
                ->selectRaw('
                    MIN(id) as id, 
                    tim, 
                    COALESCE(SUM(CASE WHEN tanggal BETWEEN ? AND ? THEN jml_perolehan ELSE 0 END), 0) as total_perolehan_2024,
                    COALESCE(SUM(CASE WHEN tanggal BETWEEN ? AND ? THEN jml_perolehan ELSE 0 END), 0) as total_perolehan_2025,
                    (COALESCE(SUM(CASE WHEN tanggal BETWEEN ? AND ? THEN jml_perolehan ELSE 0 END), 0) / 1800000000 * 100) as prosentase_2024,
                    (COALESCE(SUM(CASE WHEN tanggal BETWEEN ? AND ? THEN jml_perolehan ELSE 0 END), 0) / 1800000000 * 100) as prosentase_2025
                ', [
                    $tanggal_awal_2024, $tanggal_akhir_2024,
                    $tanggal_awal_2025, $tanggal_akhir_2025,
                    $tanggal_awal_2024, $tanggal_akhir_2024,
                    $tanggal_awal_2025, $tanggal_akhir_2025,
                ])
                ->groupBy('tim')
            )
            ->columns([
                TextColumn::make('tim')->label('Tim')->searchable(),
                TextColumn::make('total_perolehan_2024')->label('Total Perolehan 2024 (Rp)')->formatStateUsing(fn ($record) => 'Rp ' . number_format($record->total_perolehan_2024, 0, ',', '.'))->sortable(),
                TextColumn::make('prosentase_2024')->label('Prosentase 2024 1.5 M')->formatStateUsing(fn ($record) => number_format($record->prosentase_2024, 2))->suffix('%')->sortable(),
                TextColumn::make('total_perolehan_2025')->label('Total Perolehan 2025 (Rp)')->formatStateUsing(fn ($record) => 'Rp ' . number_format($record->total_perolehan_2025, 0, ',', '.'))->sortable(),
                TextColumn::make('prosentase_2025')->label('Prosentase 2025 1.8 M')->formatStateUsing(fn ($record) => number_format($record->prosentase_2025, 2))->suffix('%')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('hari_ramadhan')
                    ->label('Pilih Hari di Bulan Ramadhan')
                    ->options(array_combine(
                        range(1, 31),
                        array_map(fn($day) => "$day Ramadhan", range(1, 31))
                    ))
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $hari = (int) $data['value'];
                            $tanggal_2024 = date('Y-m-d', strtotime("2024-03-12 +".($hari - 1)." days"));
                            $tanggal_2025 = date('Y-m-d', strtotime("2025-03-01 +".($hari - 1)." days"));
                            $query->whereDate('tanggal', $tanggal_2024)
                                  ->orWhereDate('tanggal', $tanggal_2025);
                        }
                    }),
            
                Tables\Filters\SelectFilter::make('sum_ramadhan')
                    ->label('Total Perolehan dari 1 Ramadhan hingga Hari yang Dipilih')
                    ->options(array_combine(
                        range(1, 31),
                        array_map(fn($day) => "1 - $day Ramadhan", range(1, 31))
                    ))
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $hari = (int) $data['value'];
                            $tanggal_awal_2024 = '2024-03-12';
                            $tanggal_awal_2025 = '2025-03-01';
                            $tanggal_akhir_2024 = date('Y-m-d', strtotime("$tanggal_awal_2024 +".($hari - 1)." days"));
                            $tanggal_akhir_2025 = date('Y-m-d', strtotime("$tanggal_awal_2025 +".($hari - 1)." days"));
                            $query->whereBetween('tanggal', [$tanggal_awal_2024, $tanggal_akhir_2024])
                                  ->orWhereBetween('tanggal', [$tanggal_awal_2025, $tanggal_akhir_2025]);
                        }
                    }),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanramadhantims::route('/'),
            'create' => Pages\CreateLaporanramadhantim::route('/create'),
            'edit' => Pages\EditLaporanramadhantim::route('/{record}/edit'),
        ];
    }
}
