<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnalisisDonaturResource\Pages;
use App\Models\AnalisisDonatur;
use App\Models\LaporanPerolehan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AnalisisDonaturResource extends Resource
{
    protected static ?string $model = LaporanPerolehan::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'ANALISIS DONATUR';
    protected static ?string $modelLabel = 'Analisis Donatur';
    protected static ?string $pluralModelLabel = 'Analisis Donatur';
    protected static ?int $navigationSort = 1;

    /**
     * Gunakan no_hp sebagai record identifier
     */
    public static function getRecordRouteKeyName(): ?string
    {
        return 'no_hp';
    }

    public static function canCreate(): bool
    {
        return false; // Tidak bisa create karena ini hanya untuk analisis
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No HP')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('No HP disalin!'),

                Tables\Columns\TextColumn::make('nama_donatur')
                    ->label('Nama Donatur')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('total_donasi')
                    ->label('Total Donasi')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('jml_transaksi')
                    ->label('Jml Transaksi')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('first_donation')
                    ->label('Donasi Pertama')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_donation')
                    ->label('Donasi Terakhir')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status_aktif')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if (!$record->last_donation) return 'Tidak Diketahui';
                        $lastDonation = \Carbon\Carbon::parse($record->last_donation);
                        $daysDiff = $lastDonation->diffInDays(now());
                        
                        if ($daysDiff <= 7) return 'Aktif';
                        if ($daysDiff <= 30) return 'Normal';
                        if ($daysDiff <= 60) return 'Perlu Follow-up';
                        return 'Tidak Aktif';
                    })
                    ->colors([
                        'success' => 'Aktif',
                        'info' => 'Normal', 
                        'warning' => 'Perlu Follow-up',
                        'danger' => 'Tidak Aktif',
                        'gray' => 'Tidak Diketahui',
                    ]),
            ])
            ->defaultSort('total_donasi', 'desc')
            ->filters([
                // Filter Tahun Analisis
                Tables\Filters\SelectFilter::make('tahun_analisis')
                    ->label('Tahun Analisis')
                    ->options([
                        '2024' => '2024',
                        '2025' => '2025',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereYear('tanggal', $data['value']);
                        }
                        return $query;
                    }),

                // Filter Status Donatur
                Tables\Filters\SelectFilter::make('status_donatur')
                    ->label('Status Donatur')
                    ->options([
                        'donatur_hilang' => '😢 Donatur Hilang (2024, tdk di 2025)',
                        'donatur_baru' => '🆕 Donatur Baru 2025',
                        'tidak_aktif_7hari' => '⏰ Tidak Aktif 1 Minggu',
                        'tidak_aktif_30hari' => '⏰ Tidak Aktif 1 Bulan',
                        'tidak_aktif_60hari' => '⏰ Tidak Aktif 2 Bulan',
                        'tidak_aktif_365hari' => '⏰ Tidak Aktif 1 Tahun',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $status = $data['value'] ?? null;
                        if (!$status) return $query;

                        switch ($status) {
                            case 'donatur_hilang':
                                $donatur2025 = DB::table('laporans')
                                    ->whereYear('tanggal', 2025)
                                    ->whereNotNull('no_hp')
                                    ->where('no_hp', '!=', '')
                                    ->distinct()
                                    ->pluck('no_hp');
                                
                                $query->whereYear('tanggal', 2024)
                                    ->whereNotIn('no_hp', $donatur2025);
                                break;

                            case 'donatur_baru':
                                $donatur2024 = DB::table('laporans')
                                    ->whereYear('tanggal', 2024)
                                    ->whereNotNull('no_hp')
                                    ->where('no_hp', '!=', '')
                                    ->distinct()
                                    ->pluck('no_hp');
                                
                                $query->whereYear('tanggal', 2025)
                                    ->whereNotIn('no_hp', $donatur2024);
                                break;

                            case 'tidak_aktif_7hari':
                                $query->havingRaw('MAX(tanggal) < ?', [now()->subDays(7)->format('Y-m-d')]);
                                break;

                            case 'tidak_aktif_30hari':
                                $query->havingRaw('MAX(tanggal) < ?', [now()->subDays(30)->format('Y-m-d')]);
                                break;

                            case 'tidak_aktif_60hari':
                                $query->havingRaw('MAX(tanggal) < ?', [now()->subDays(60)->format('Y-m-d')]);
                                break;

                            case 'tidak_aktif_365hari':
                                $query->havingRaw('MAX(tanggal) < ?', [now()->subDays(365)->format('Y-m-d')]);
                                break;
                        }
                        return $query;
                    }),

                // Filter Range Tanggal
                Tables\Filters\Filter::make('range_tanggal')
                    ->form([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['dari_tanggal'])) {
                            $query->whereDate('tanggal', '>=', $data['dari_tanggal']);
                        }
                        if (!empty($data['sampai_tanggal'])) {
                            $query->whereDate('tanggal', '<=', $data['sampai_tanggal']);
                        }
                        return $query;
                    }),
            ])
            ->actions([
                // Action WhatsApp
                Action::make('whatsapp')
                    ->label('Chat WA')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->url(fn ($record) => 'https://wa.me/' . $record->no_hp)
                    ->openUrlInNewTab()
                    ->color('success'),

                // Action Lihat Riwayat
                Action::make('riwayat')
                    ->label('Riwayat')
                    ->icon('heroicon-o-document-text')
                    ->modalHeading(fn ($record) => 'Riwayat Donasi: ' . $record->nama_donatur)
                    ->modalContent(function ($record) {
                        $riwayat = DB::table('laporans')
                            ->where('no_hp', $record->no_hp)
                            ->orderByDesc('tanggal')
                            ->limit(20)
                            ->get();
                        
                        return view('filament.resources.analisis-donatur.riwayat-modal', [
                            'riwayat' => $riwayat,
                            'record' => $record,
                        ]);
                    })
                    ->modalWidth('4xl')
                    ->color('info'),
            ])
            ->headerActions([
                // Export Excel
                Action::make('export')
                    ->label('Export ke Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form([
                        Select::make('tahun_export')
                            ->label('Tahun')
                            ->options([
                                '2024' => '2024',
                                '2025' => '2025',
                                'all' => 'Semua Tahun',
                            ])
                            ->default('2025'),
                        Select::make('status_export')
                            ->label('Status Donatur')
                            ->options([
                                'all' => 'Semua Donatur',
                                'top_donatur' => 'Top Donatur',
                                'donatur_hilang' => 'Donatur Hilang',
                                'donatur_baru' => 'Donatur Baru',
                                'tidak_aktif_30hari' => 'Tidak Aktif 1 Bulan',
                            ])
                            ->default('all'),
                        Select::make('limit_export')
                            ->label('Jumlah Data')
                            ->options([
                                '100' => '100 Data',
                                '500' => '500 Data',
                                '1000' => '1000 Data',
                                'all' => 'Semua Data',
                            ])
                            ->default('500'),
                    ])
                    ->action(function (array $data) {
                        return static::exportToExcel($data);
                    })
                    ->color('success'),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Tidak ada data donatur')
            ->emptyStateDescription('Silakan ubah filter untuk melihat data donatur.');
    }

    /**
     * Export data ke Excel
     */
    public static function exportToExcel(array $data)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = ['No', 'No HP', 'Nama Donatur', 'Total Donasi', 'Jumlah Transaksi', 'Donasi Pertama', 'Donasi Terakhir', 'Status'];
        foreach ($headers as $index => $header) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue("{$column}1", $header);
        }

        // Style header
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF4CAF50');
        $sheet->getStyle('A1:H1')->getFont()->getColor()->setARGB('FFFFFFFF');

        // Build query
        $query = DB::table('laporans')
            ->select(
                'no_hp',
                DB::raw('MAX(nama_donatur) as nama_donatur'),
                DB::raw('SUM(jml_perolehan) as total_donasi'),
                DB::raw('COUNT(*) as jml_transaksi'),
                DB::raw('MIN(tanggal) as first_donation'),
                DB::raw('MAX(tanggal) as last_donation')
            )
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '');

        // Filter tahun
        if ($data['tahun_export'] !== 'all') {
            $query->whereYear('tanggal', $data['tahun_export']);
        }

        // Filter status
        if ($data['status_export'] === 'donatur_hilang') {
            $donaturTahunIni = DB::table('laporans')
                ->whereYear('tanggal', 2025)
                ->distinct()
                ->pluck('no_hp');
            $query->whereYear('tanggal', '<', 2025)
                ->whereNotIn('no_hp', $donaturTahunIni);
        } elseif ($data['status_export'] === 'donatur_baru') {
            $donaturSebelumnya = DB::table('laporans')
                ->whereYear('tanggal', '<', 2025)
                ->distinct()
                ->pluck('no_hp');
            $query->whereYear('tanggal', 2025)
                ->whereNotIn('no_hp', $donaturSebelumnya);
        } elseif ($data['status_export'] === 'tidak_aktif_30hari') {
            $query->havingRaw('MAX(tanggal) < ?', [now()->subDays(30)->format('Y-m-d')]);
        }

        $query->groupBy('no_hp')
            ->orderByDesc('total_donasi');

        // Limit
        if ($data['limit_export'] !== 'all') {
            $query->limit((int) $data['limit_export']);
        }

        $records = $query->get();

        // Fill data
        $row = 2;
        $no = 1;
        foreach ($records as $record) {
            $lastDonation = \Carbon\Carbon::parse($record->last_donation);
            $daysDiff = $lastDonation->diffInDays(now());
            
            if ($daysDiff <= 7) $status = 'Aktif';
            elseif ($daysDiff <= 30) $status = 'Normal';
            elseif ($daysDiff <= 60) $status = 'Perlu Follow-up';
            else $status = 'Tidak Aktif';

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValueExplicit("B{$row}", $record->no_hp, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("C{$row}", $record->nama_donatur);
            $sheet->setCellValue("D{$row}", $record->total_donasi);
            $sheet->setCellValue("E{$row}", $record->jml_transaksi);
            $sheet->setCellValue("F{$row}", $record->first_donation);
            $sheet->setCellValue("G{$row}", $record->last_donation);
            $sheet->setCellValue("H{$row}", $status);

            $row++;
            $no++;
        }

        // Format currency
        $sheet->getStyle('D2:D' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');

        // Auto width
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Save and download
        $fileName = 'analisis_donatur_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filePath = storage_path("app/public/{$fileName}");
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnalisisDonatur::route('/'),
        ];
    }
}
