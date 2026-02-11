<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenyebaranTokoResource\Pages;
use App\Models\PenyebaranToko;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class PenyebaranTokoResource extends Resource
{
    protected static ?string $model = PenyebaranToko::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'DATA KENCLENG';
    protected static ?string $navigationLabel = 'Penyebaran Kencleng';
    protected static ?string $pluralLabel = 'Penyebaran Kencleng';
    protected static ?string $modelLabel = 'Penyebaran Kencleng';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal_registrasi')
                    ->required()
                    ->label('Tanggal Registrasi'),

                Forms\Components\TextInput::make('nama_cs')
                    ->required()
                    ->label('Nama CS'),

                Forms\Components\TextInput::make('nomor_kencleng')
                    ->required()
                    ->label('Nomor Kencleng'),

                Forms\Components\TextInput::make('nama_toko')
                    ->required()
                    ->label('Nama Toko'),

                Forms\Components\TextInput::make('nama_donatur')
                    ->required()
                    ->label('Nama Donatur'),

                Forms\Components\TextInput::make('no_hp')
                    ->required()
                    ->label('No. HP'),

                Forms\Components\Textarea::make('alamat')
                    ->required()
                    ->label('Alamat'),

                Forms\Components\TextInput::make('latitude')
                    ->label('Latitude'),

                Forms\Components\TextInput::make('longitude')
                    ->label('Longitude'),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan'),

                Forms\Components\Select::make('status')
                    ->options([
                        'Di terima' => 'Di terima',
                        'Di tolak' => 'Di tolak',
                    ])
                    ->label('Status'),

                Forms\Components\FileUpload::make('foto_base64')
                    ->label('Foto')
                    ->image()
                    ->directory('foto_base64')
                    ->disk('public')
                    ->visibility('public'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null) // 🔒 Nonaktifkan clickable row
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_registrasi')->label('Tanggal Registrasi')->date(),
                Tables\Columns\TextColumn::make('nama_cs')->label('Nama CS')->searchable(),
                Tables\Columns\TextColumn::make('nomor_kencleng')->label('Nomor Kencleng'),
                Tables\Columns\TextColumn::make('nama_toko')->label('Nama Toko')->searchable(),
                Tables\Columns\TextColumn::make('nama_donatur')->label('Nama Donatur')->searchable(),
                Tables\Columns\TextColumn::make('no_hp')->label('No. HP'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Di terima' => 'success',
                        'Di tolak' => 'danger',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('foto_base64')
                    ->label('Link Foto')
                    ->url(fn ($record) => "https://abbarat.abdashboard.com/{$record->foto_base64}")
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn ($state) => 'Klik untuk lihat foto'),

                Tables\Columns\TextColumn::make('alamat')->label('Alamat'),
                Tables\Columns\TextColumn::make('keterangan')->label('Keterangan')->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Di tolak' => 'Di tolak',
                        'Di terima' => 'Di terima',
                    ]),

                Tables\Filters\Filter::make('tanggal_registrasi')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_registrasi')
                            ->default(now())
                            ->label('Tanggal Registrasi'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['tanggal_registrasi'] ?? null,
                                fn ($query, $date) => $query->whereDate('tanggal_registrasi', $date)
                            );
                    }),
            ])

            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])

            ->headerActions([
                \Filament\Tables\Actions\Action::make('export')
                    ->label('Export ke Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')->label('Tanggal Mulai'),
                        Forms\Components\DatePicker::make('end_date')->label('Tanggal Akhir'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Di terima' => 'Di terima',
                                'Di tolak' => 'Di tolak',
                            ])
                            ->placeholder('Semua Status'),
                    ])
                    ->action(function (array $data) {
                        $spreadsheet = new Spreadsheet();
                        $sheet = $spreadsheet->getActiveSheet();

                        // Header
                        $headers = [
                            'Tanggal Registrasi', 'Nama CS', 'Nomor Kencleng', 'Nama Toko',
                            'Nama Donatur', 'No. HP', 'Alamat', 'Latitude', 'Longitude',
                            'Keterangan', 'Status', 'Link Foto'
                        ];

                        foreach ($headers as $index => $header) {
                            $col = Coordinate::stringFromColumnIndex($index + 1);
                            $sheet->setCellValue("{$col}1", $header);
                            $sheet->getStyle("{$col}1")->getFont()->setBold(true);
                        }

                        // Query dengan filter
                        $query = PenyebaranToko::query();

                        if (!empty($data['start_date'])) {
                            $query->whereDate('tanggal_registrasi', '>=', $data['start_date']);
                        }
                        if (!empty($data['end_date'])) {
                            $query->whereDate('tanggal_registrasi', '<=', $data['end_date']);
                        }
                        if (!empty($data['status'])) {
                            $query->where('status', $data['status']);
                        }

                        $records = $query->get();

                        // Isi data
                        $row = 2;
                        foreach ($records as $record) {
                            $sheet->setCellValue("A{$row}", $record->tanggal_registrasi);
                            $sheet->setCellValue("B{$row}", $record->nama_cs);
                            $sheet->setCellValue("C{$row}", $record->nomor_kencleng);
                            $sheet->setCellValue("D{$row}", $record->nama_toko);
                            $sheet->setCellValue("E{$row}", $record->nama_donatur);
                            $sheet->setCellValue("F{$row}", $record->no_hp);
                            $sheet->setCellValue("G{$row}", $record->alamat);
                            $sheet->setCellValue("H{$row}", $record->latitude ?? '');
                            $sheet->setCellValue("I{$row}", $record->longitude ?? '');
                            $sheet->setCellValue("J{$row}", $record->keterangan ?? '');
                            $sheet->setCellValue("K{$row}", $record->status ?? '');
                            $sheet->setCellValue("L{$row}", $record->foto_base64 ? "https://abbarat.abdashboard.com/{$record->foto_base64}" : '');
                            $row++;
                        }

                        // Auto size columns
                        foreach (range('A', 'L') as $col) {
                            $sheet->getColumnDimension($col)->setAutoSize(true);
                        }

                        // Simpan & download
                        $fileName = 'penyebaran_kencleng_' . now()->format('Y-m-d_His') . '.xlsx';
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenyebaranTokos::route('/'),
            'create' => Pages\CreatePenyebaranToko::route('/create'),
            'edit' => Pages\EditPenyebaranToko::route('/{record}/edit'),
        ];
    }
}
