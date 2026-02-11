<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsensHResource\Pages;
use App\Models\AbsenCs;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;

class AbsensHResource extends Resource
{
    protected static ?string $model = AbsenCs::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Data Absensi';
    protected static ?string $navigationLabel = 'Absen Harian CS';
    protected static ?string $pluralLabel = 'Daftar Absen Harian';
    protected static ?string $modelLabel = 'Absen Harian CS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_cs')
                    ->label('Nama CS')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('status_kehadiran')
                    ->label('Status Kehadiran')
                    ->options([
                        'hadir' => 'Hadir',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'alpa' => 'Alpa',
                    ])
                    ->required(),

                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required(),

                Forms\Components\TimePicker::make('jam')
                    ->label('Jam')
                    ->required(),

                Forms\Components\Select::make('tipe_absen')
                    ->label('Tipe Absen')
                    ->options([
                        'masuk' => 'Masuk',
                        'pulang' => 'Pulang',
                    ])
                    ->required(),

                Forms\Components\FileUpload::make('foto')
                    ->label('Foto Absen')
                    ->disk('public')
                    ->directory('absen_foto')
                    ->image()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('lokasi')
                    ->label('Lokasi')
                    ->nullable()
                    ->maxLength(255),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null) // Nonaktifkan klik ke detail
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_cs')
                    ->label('Nama CS')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status_kehadiran')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hadir' => 'success',
                        'izin'  => 'warning',
                        'sakit' => 'info',
                        'alpa'  => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jam')
                    ->label('Jam')
                    ->time()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipe_absen')
                    ->label('Tipe')
                    ->sortable(),

                Tables\Columns\TextColumn::make('foto')
                    ->label('Foto')
                    ->url(fn ($record) => "https://abbarat.abdashboard.com/{$record->foto}")
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn () => '📎 Lihat Foto')
                    ->color('primary')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('lokasi')
                    ->label('Lokasi')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Akhir'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['start_date'], fn ($q, $date) => $q->whereDate('tanggal', '>=', $date))
                        ->when($data['end_date'], fn ($q, $date) => $q->whereDate('tanggal', '<=', $date))
                    ),

                Tables\Filters\SelectFilter::make('nama_cs')
                    ->label('Nama CS')
                    ->options(fn () => AbsenCs::query()
                        ->distinct()
                        ->pluck('nama_cs', 'nama_cs')),

                Tables\Filters\SelectFilter::make('tipe_absen')
                    ->label('Tipe Absen')
                    ->options([
                        'masuk' => 'Masuk',
                        'pulang' => 'Pulang',
                    ]),

                Tables\Filters\SelectFilter::make('status_kehadiran')
                    ->label('Status Kehadiran')
                    ->options([
                        'hadir' => 'Hadir',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'alpa' => 'Alpa',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                \Filament\Tables\Actions\Action::make('export')
                    ->label('Export ke Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Akhir'),
                        Forms\Components\Select::make('nama_cs')
                            ->label('Nama CS')
                            ->options(AbsenCs::query()
                                ->distinct()
                                ->pluck('nama_cs', 'nama_cs')
                                ->toArray())
                            ->searchable()
                            ->placeholder('Semua CS'),
                        Forms\Components\Select::make('tipe_absen')
                            ->label('Tipe Absen')
                            ->options([
                                'masuk' => 'Masuk',
                                'pulang' => 'Pulang',
                            ])
                            ->placeholder('Semua Tipe'),
                        Forms\Components\Select::make('status_kehadiran')
                            ->label('Status Kehadiran')
                            ->options([
                                'hadir' => 'Hadir',
                                'izin' => 'Izin',
                                'sakit' => 'Sakit',
                                'alpa' => 'Alpa',
                            ])
                            ->placeholder('Semua Status'),
                    ])
                    ->action(function (array $data) {
                        $spreadsheet = new Spreadsheet();
                        $sheet = $spreadsheet->getActiveSheet();

                        // Header
                        $headers = [
                            'ID', 'Nama CS', 'Status', 'Tanggal', 'Jam', 'Tipe Absen',
                            'Link Foto', 'Lokasi', 'Keterangan'
                        ];

                        foreach ($headers as $index => $header) {
                            $col = Coordinate::stringFromColumnIndex($index + 1);
                            $sheet->setCellValue("{$col}1", $header);
                            $sheet->getStyle("{$col}1")->getFont()->setBold(true);
                        }

                        // Query dengan filter
                        $query = AbsenCs::query();

                        if (!empty($data['start_date'])) {
                            $query->whereDate('tanggal', '>=', $data['start_date']);
                        }
                        if (!empty($data['end_date'])) {
                            $query->whereDate('tanggal', '<=', $data['end_date']);
                        }
                        if (!empty($data['nama_cs'])) {
                            $query->where('nama_cs', $data['nama_cs']);
                        }
                        if (!empty($data['tipe_absen'])) {
                            $query->where('tipe_absen', $data['tipe_absen']);
                        }
                        if (!empty($data['status_kehadiran'])) {
                            $query->where('status_kehadiran', $data['status_kehadiran']);
                        }

                        $absensi = $query->get();

                        // Isi data
                        $row = 2;
                        foreach ($absensi as $absen) {
                            $sheet->setCellValue("A{$row}", $absen->id);
                            $sheet->setCellValue("B{$row}", $absen->nama_cs);
                            $sheet->setCellValue("C{$row}", $absen->status_kehadiran);
                            $sheet->setCellValue("D{$row}", $absen->tanggal);
                            $sheet->setCellValue("E{$row}", $absen->jam);
                            $sheet->setCellValue("F{$row}", $absen->tipe_absen);
                            $sheet->setCellValue("G{$row}", $absen->foto ? "https://abbarat.abdashboard.com/{$absen->foto}" : '');
                            $sheet->setCellValue("H{$row}", $absen->lokasi ?? '');
                            $sheet->setCellValue("I{$row}", $absen->keterangan ?? '');
                            // $sheet->setCellValue("J{$row}", $absen->created_at);
                            // $sheet->setCellValue("K{$row}", $absen->updated_at);
                            $row++;
                        }

                        // Auto size columns
                        foreach (range('A', 'K') as $col) {
                            $sheet->getColumnDimension($col)->setAutoSize(true);
                        }

                        // Simpan & download
                        $fileName = 'absensi_cs_' . now()->format('Y-m-d_His') . '.xlsx';
                        $path = storage_path("app/public/{$fileName}");
                        $writer = new Xlsx($spreadsheet);
                        $writer->save($path);

                        return response()->download($path)->deleteFileAfterSend(true);
                    }),
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
            // Tambahkan relasi jika diperlukan
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbsensHS::route('/'),
            'create' => Pages\CreateAbsensH::route('/create'),
            'edit' => Pages\EditAbsensH::route('/{record}/edit'),
        ];
    }
}