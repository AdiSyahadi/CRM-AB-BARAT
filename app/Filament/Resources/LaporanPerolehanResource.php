<?php

namespace App\Filament\Resources;
use App\Filament\Resources\LaporanPerolehanResource\Pages;
use App\Filament\Resources\LaporanPerolehanResource\RelationManagers;
use App\Models\LaporanPerolehan;
use App\Models\Donatur;
use App\Models\CustomerService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use App\Models\CSMonthlyPerformance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LaporanPerolehanResource extends Resource
{
    protected static ?string $model = LaporanPerolehan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'REALTIME LAPORAN';
    //protected static ?string $navigationGroup = 'DATA LAPORAN';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema([ // Membuat grid 2 kolom
                    Forms\Components\DatePicker::make('tanggal')
                        ->label('Tanggal')
                        ->required()
                        ->default(now()),
                    
                    Forms\Components\Select::make('tim')
                        ->label('Tim')
                        ->options(CustomerService::query()->pluck('team', 'team'))
                        ->required(),
                    
                    Forms\Components\Select::make('nama_cs')
                        ->label('Nama CS')
                        ->options(CustomerService::query()->pluck('name', 'name'))
                        ->required(),
                    
                        Forms\Components\Select::make('perolehan_jam')
                        ->label('Perolehan Jam')
                        ->options([
                            '08.00-09.00 WIB' => '08.00-09.00 WIB',
                            '09.00-10.00 WIB' => '09.00-10.00 WIB',
                            '10.00-11.00 WIB' => '10.00-11.00 WIB',
                            '11.00-12.00 WIB' => '11.00-12.00 WIB',
                            '12.00-13.00 WIB' => '12.00-13.00 WIB',
                            '13.00-14.00 WIB' => '13.00-14.00 WIB',
                            '14.00-15.00 WIB' => '14.00-15.00 WIB',
                            '15.00-16.00 WIB' => '15.00-16.00 WIB',
                            '16.00-17.00 WIB' => '16.00-17.00 WIB',
                            '17.00-24.00 WIB' => '17.00-24.00 WIB',
                        ])
                        ->required(),                    
                    
                    Forms\Components\TextInput::make('jml_database')
                        ->label('Database yang digunakan')
                        ->numeric(),
                    
                    Forms\Components\TextInput::make('jml_perolehan')
                        ->label('Jumlah Perolehan')
                        ->numeric()
                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ','),

                    Forms\Components\Section::make('PILIH HASIL DARI')
                        ->schema([

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('hasil_dari')
                                    ->label('Hasil Dari')
                                    ->options([
                                        'Cross Selling' => 'Cross Selling',
                                        'Infaq' => 'Infaq',
                                        'Wakaf' => 'Wakaf',
                                        'Produk' => 'Produk',
                                        'Zakat' => 'Zakat',
                                        'Platform' => 'Platform',]),
                                // Select untuk Program Utama
                                Forms\Components\Select::make('program_utama')
                                    ->label('Jenis Waktu')
                                    ->options([
                                        'Subuh' => 'Subuh',
                                        'Jumat' => 'Jumat',
                                        'Harian' =>'Harian', 
                                    ]),
                            ]),
                        
                        ]),
                    
                    Forms\Components\Section::make('HASIL DARI')
                        ->schema([
                            Forms\Components\Grid::make(6)->schema([
                    
                                // Select untuk Cross Selling
                                Forms\Components\Select::make('prg_cross_selling')
                                    ->label('Cross Selling')
                                    ->options([
                                        'Produk' => 'Produk',
                                        'Program AB Barat' => 'Program AB Barat',
                                        'Program Cabang' => 'Program Cabang',
                                        'Wakaf AB Barat' => 'Wakaf AB Barat',
                                        'AB Chicken/Berkah Box' => 'AB Chicken/Berkah Box',
                                        'Program Ramadhan' => 'Program Ramadhan',
                                        'Wakaf Cabang' => 'Wakaf Cabang',
                                        'Wakaf Quran' => 'Wakaf Quran',
                                        'Umroh/haji' => 'Umroh/haji',
                                        'Qurban' => 'Qurban',
                                        'Palestina' => 'Palestina',
                                        'TOBETOSI'=>'TOBETOSI',
                                        'Dana Sosial'=>'Dana Sosial',
                                        'Sedekah Daging'=>'Sedekah Daging',
                                    ]),
                    
                                // Select untuk Zakat
                                Forms\Components\Select::make('zakat')
                                    ->label('Zakat')
                                    ->options([
                                        'Zakat Maal' => 'Zakat Maal',
                                        'Zakat Fitrah' => 'Zakat Fitrah',
                                        'Zakat Penghasilan' => 'Zakat Penghasilan',
                                        'Zakat Fidyah' => 'Zakat Fidyah',
                                        'Dana Subhat' => 'Dana Subhat',
                                    ]),
                                    Forms\Components\Select::make('wakaf')
                                    ->label('Wakaf')
                                    ->options([
                                        'Wakaf pembangunan' => 'Wakaf pembangunan',
                                        'Pembebasan lahan' => 'Pembebasan lahan',
                                        'Pengadaaan fasilitas' => 'Pengadaaan fasilitas',
                                    ]),
                    
                                // Select untuk Penjualan Barang (Khusus AB Store)
                                Forms\Components\Select::make('nama_produk')
                                    ->label('Nama Produk')
                                    ->options([
                                        'Buku Buya Yahya' => 'Buku Buya Yahya',
                                        'Rendang Santri' => 'Rendang Santri',
                                        'Al-Quran' => 'Al-Quran',
                                        'Madu ANH' => 'Madu ANH',
                                        'Produk MH' => 'Produk MH',
                                        'Wakaf Tasbih' => 'Wakaf Tasbih',
                                        'Infaq Qurma' => 'Infaq Qurma',
                                        'Mukenah' => 'Mukenah',
                                    ]),
                                Forms\Components\Select::make('nama_platform')
                                    ->label('Platform')
                                    ->options([
                                        'Bantu Bersama' => 'Bantu Bersama',
                                        'Sharing Happyness' => 'Sharing Happyness',
                                        'Sedekah Online' => 'Sedekah Online',
                                        'KITA BISA' => 'KITA BISA',
                                        'AMAL SOLEH' => 'AMAL SOLEH',
                                        'CHANNELING' => 'CHANNELING',
                                        'PARTNERSHIP' => 'PARTNERSHIP',
                                        'DONASI ONLINE' => 'DONASI ONLINE',
                                    ]),
                                 // Select untuk Program E-Commerce
                                Forms\Components\Select::make('e_commerce')
                                    ->label('E-Commerce')
                                    ->options([
                                        'Shopee' => 'Shopee',
                                        'Lazada' => 'Lazada',
                                        'Tokopedia' =>'Tokopedia', 
                                    ]),
                                
                            ]),
                        ])
                    ]),
                
                
                Forms\Components\Section::make('DATA DONATUR')
                    ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        
//                            ->required(),
//Forms\Components\TextInput::make('did')
                               // ->label('DID')
                               // ->required(),
                            //Forms\Components\Select::make('kode_negara')
//                                ->label('Kode Negara')
//                                ->options([
//                                    '+62' => 'ID +62',
//                                    '+966' => 'ARB +966',
//                                    '+852' => 'HK +852',
//                                    '+886' => 'TWN +886',
                                    // Tambahkan kode negara lainnya di sini
 //                               ])
 //                               ->default('+62'),
                            
                            
                            Forms\Components\TextInput::make('no_hp')
                                ->label('Nomor HP')
                                ->tel()
                                ->numeric()
                                ->debounce(500) // Menunggu 500ms setelah input terakhir
                                ->reactive() // Agar field ini bereaksi terhadap input
                                ->afterStateUpdated(function ($state, callable $set) {
                                    // Cari donatur berdasarkan nomor HP
                                    $donatur = \App\Models\Donatur::where('no_hp', $state)->first();
    
                                    if ($donatur) {
                                        // Set nilai untuk field lain jika donatur ditemukan
                                        $set('nama_donatur', $donatur->nama_donatur);
                                        $set('jenis_kelamin', $donatur->jenis_kelamin);
                                        $set('email', $donatur->email);
                                        $set('alamat', $donatur->alamat);
                                        $set('sosmed_account', $donatur->sosmed_account);
                                        $set('program', $donatur->program);
                                        $set('channel', $donatur->channel);
                                        $set('fundraiser', $donatur->fundraiser);
                                        $set('kat_donatur', $donatur->kat_donatur);
                                        $set('did', $donatur->did);
                                        //$set('nama_cs', $donatur->nama_cs);
                                    } else {
                                        // Kosongkan field jika data tidak ditemukan
                                        $set('nama_donatur', '');
                                        $set('jenis_kelamin', '');
                                        $set('email', '');
                                        $set('alamat', '');
                                        $set('sosmed_account', '');
                                        $set('program', '');
                                        $set('channel', '');
                                        $set('fundraiser', '');
                                        $set('kat_donatur', '');
                                        $set('did', '');
                                        //$set('nama_cs', '');
                                    }
                                }),
                            Forms\Components\Select::make('kat_donatur')
                            ->label('Kategori Donatur')
                            ->options([
                                'Retail' => 'Retail',
                                'Corporate' => 'Corporate',
                                'Community' => 'Community',
                            ]),
                            Forms\Components\TextInput::make('did')
                                    ->label('DID'),
                                    //->disabled(),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('nama_donatur')
                                    ->label('Nama Donatur'),
                            
                                Forms\Components\Select::make('jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                       'Laki-Laki' => 'Laki-Laki',
                                        'Perempuan' => 'Perempuan',
                                    ]),
                            
                                //Forms\Components\TextInput::make('email')
                                    //->label('Email')
                                    //->email(),
                            
                                //Forms\Components\TextInput::make('alamat')
                                    //->label('Alamat'),
                            
                                //Forms\Components\TextInput::make('sosmed_account')
                                    //->label('Sosmed Account'),
                            
                                //Forms\Components\TextInput::make('program')
                                    //->label('Program'),
                            
                                //Forms\Components\TextInput::make('channel')
                                    //->label('Channel'),
                            
                                //Forms\Components\TextInput::make('fundraiser')
                                    //->label('Fundraiser'),
                            
                                    ]),
                        ]),
                    //Forms\Components\Textarea::make('keterangan')
                        //->label('Keterangan'),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->recordUrl(null) // 🔒 Menonaktifkan clickable row
        ->columns([
            Tables\Columns\TextColumn::make('id')
                ->label('ID')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('tanggal')
                ->label('Tanggal')
                ->date()
                ->sortable(),

            Tables\Columns\TextColumn::make('tim')
                ->label('Tim')
                ->sortable()
                ->searchable(),
            
            Tables\Columns\TextColumn::make('did')
                ->label('DID')
                ->sortable(),

            Tables\Columns\TextColumn::make('nama_cs')
                ->label('Nama CS')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('perolehan_jam')
                ->label('Perolehan Jam')
                ->sortable(),

            Tables\Columns\TextColumn::make('jml_database')
                ->label('Jumlah Database')
                ->sortable(),

            Tables\Columns\TextColumn::make('jml_perolehan')
                ->label('Jumlah Perolehan')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('nama_donatur')
                ->label('Nama Donatur')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('kode_negara')
                ->label('Kode Negara')
                ->sortable(),

            Tables\Columns\TextColumn::make('no_hp')
                ->label('No HP')
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('program_utama')
                ->label('Program utama')
                ->sortable(),

            Tables\Columns\TextColumn::make('zakat')
                ->label('Program Zakat')
                ->sortable(),

            Tables\Columns\TextColumn::make('prg_cross_selling')
                ->label('Program Cross Selling')
                ->sortable(),

            Tables\Columns\TextColumn::make('nama_produk')
                ->label('Nama Produk')
                ->sortable(),

            Tables\Columns\TextColumn::make('nama_platform')
                ->label('Nama Platform')
                ->sortable(),

            Tables\Columns\TextColumn::make('kat_donatur')
                ->label('Kategori Donatur')
                ->sortable(),

            Tables\Columns\TextColumn::make('jenis_kelamin')
                ->label('Jenis Kelamin')
                ->sortable(),

            Tables\Columns\TextColumn::make('email')
                ->label('Email')
                ->sortable(),

            Tables\Columns\TextColumn::make('sosmed_account')
                ->label('Sosial Media Account')
                ->sortable(),

            Tables\Columns\TextColumn::make('alamat')
                ->label('Alamat')
                ->sortable(),

            Tables\Columns\TextColumn::make('program')
                ->label('Program')
                ->sortable(),

            Tables\Columns\TextColumn::make('channel')
                ->label('Channel')
                ->sortable(),

            Tables\Columns\TextColumn::make('fundraiser')
                ->label('Fundraiser')
                ->sortable(),

            Tables\Columns\TextColumn::make('keterangan')
                ->label('Keterangan')
                ->sortable(),

            Tables\Columns\TextColumn::make('updated_at')
                ->label('Updated At')
                ->date()
                ->sortable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Created At')
                ->date()
                ->sortable(),
        ])
        ->filters([
            Tables\Filters\Filter::make('tanggal')
                ->form([
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Tanggal Mulai'),
                    Forms\Components\DatePicker::make('end_date')
                        ->label('Tanggal Akhir'),
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
            // Filter untuk jumlah donasi
            Tables\Filters\Filter::make('Jumlah Donasi')
                ->form([
                    Forms\Components\Select::make('donasi_filter')
                        ->label('Jumlah Donasi')
                        ->options([
                            'lebih' => 'Lebih dari 500 ribu',
                            'kurang' => 'Kurang dari 500 ribu',
                        ]),
                ])
                ->query(function (Builder $query, array $data) {
                    if (!empty($data['donasi_filter'])) {
                        if ($data['donasi_filter'] === 'lebih') {
                            $query->where('jml_perolehan', '>=', 500000);
                        } elseif ($data['donasi_filter'] === 'kurang') {
                            $query->where('jml_perolehan', '<', 500000);
                        }
                    }
                    return $query;
                }),
        
            // Filter untuk nama CS
            Tables\Filters\Filter::make('Nama CS')
                ->form([
                    Forms\Components\Select::make('nama_cs')
                        ->label('Nama CS')
                        ->options(
                            \App\Models\LaporanPerolehan::query()
                                ->distinct()
                                ->pluck('nama_cs', 'nama_cs')
                                ->toArray()
                        )
                        ->searchable(),
                ])
                ->query(function (Builder $query, array $data) {
                    if (!empty($data['nama_cs'])) {
                        $query->where('nama_cs', $data['nama_cs']);
                    }
                    return $query;
                }),
        
            // Filter untuk nama donatur unik
            //Tables\Filters\Filter::make('Nama Donatur Unik')
                //->query(function (Builder $query) {
                  //  $query->groupBy('no_hp');
                //    return $query;
                //}),
        ])


        ->actions([ 
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
            
        ])
        ->headerActions([
            Action::make('export')
                ->label('Export ke Excel')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai'),
                    DatePicker::make('end_date')
                        ->label('Tanggal Akhir'),
                    Select::make('nama_cs')
                        ->label('Nama CS')
                        ->options(LaporanPerolehan::pluck('nama_cs', 'nama_cs')->unique()->toArray())
                        ->placeholder('Pilih Nama CS')
                        ->searchable(),                    
                    Select::make('donasi_filter')
                        ->label('Jumlah Donasi')
                        ->options([
                            'lebih' => 'Lebih dari 500 ribu',
                            'kurang' => 'Kurang dari 500 ribu',
                        ])
                        ->placeholder('Pilih Filter Donasi'),
                ])
                ->action(function (array $data) {
                    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();
        
                    // Set headers sesuai tabel
                    $headers = [
                        'ID', 'Tanggal', 'Tim', 'DID', 'Nama CS', 'Perolehan Jam', 'Jumlah Database',
                        'Jumlah Perolehan', 'Nama Donatur', 'Kode Negara', 'No HP', 'Program Utama',
                        'Program Zakat', 'Program Cross Selling', 'Nama Produk', 'Nama Platform',
                        'Kategori Donatur', 'Jenis Kelamin', 'Email', 'Sosial Media Account',
                        'Alamat', 'Program', 'Channel', 'Fundraiser', 'Keterangan', 'Updated At', 'Created At',
                    ];
        
                    // Isi header ke dalam spreadsheet
                    foreach ($headers as $index => $header) {
                        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
                        $sheet->setCellValue("{$column}1", $header);
                    }
        
                    // Ambil data dari tabel dengan filter
                    $reports = LaporanPerolehan::query()
                        ->when($data['start_date'], fn($query) => $query->whereDate('tanggal', '>=', $data['start_date']))
                        ->when($data['end_date'], fn($query) => $query->whereDate('tanggal', '<=', $data['end_date']))
                        ->when($data['nama_cs'], fn($query) => $query->where('nama_cs', $data['nama_cs']))
                        ->when($data['donasi_filter'], function ($query) use ($data) {
                            if ($data['donasi_filter'] === 'lebih') {
                                $query->where('jml_perolehan', '>=', 500000);
                            } elseif ($data['donasi_filter'] === 'kurang') {
                                $query->where('jml_perolehan', '<', 500000);
                            }
                        })
                        ->get();
        
                    // Masukkan data ke dalam spreadsheet
                    $row = 2;
                    foreach ($reports as $report) {
                        $sheet->setCellValue("A$row", $report->id);
                        $sheet->setCellValue("B$row", $report->tanggal);
                        $sheet->setCellValue("C$row", $report->tim);
                        $sheet->setCellValue("D$row", $report->did);
                        $sheet->setCellValue("E$row", $report->nama_cs);
                        $sheet->setCellValue("F$row", $report->perolehan_jam);
                        $sheet->setCellValue("G$row", $report->jml_database);
                        $sheet->setCellValue("H$row", $report->jml_perolehan);
                        $sheet->setCellValue("I$row", $report->nama_donatur);
                        $sheet->setCellValue("J$row", $report->kode_negara);
                        $sheet->setCellValue("K$row", $report->no_hp);
                        $sheet->setCellValue("L$row", $report->program_utama);
                        $sheet->setCellValue("M$row", $report->program_zakat);
                        $sheet->setCellValue("N$row", $report->program_cross_selling);
                        $sheet->setCellValue("O$row", $report->nama_produk);
                        $sheet->setCellValue("P$row", $report->nama_platform);
                        $sheet->setCellValue("Q$row", $report->kategori_donatur);
                        $sheet->setCellValue("R$row", $report->jenis_kelamin);
                        $sheet->setCellValue("S$row", $report->email);
                        $sheet->setCellValue("T$row", $report->sosial_media_account);
                        $sheet->setCellValue("U$row", $report->alamat);
                        $sheet->setCellValue("V$row", $report->program);
                        $sheet->setCellValue("W$row", $report->channel);
                        $sheet->setCellValue("X$row", $report->fundraiser);
                        $sheet->setCellValue("Y$row", $report->keterangan);
                        $sheet->setCellValue("Z$row", $report->updated_at);
                        $sheet->setCellValue("AA$row", $report->created_at);
        
                        $row++;
                    }
        
                    // Simpan dan ekspor file Excel
                    $fileName = 'laporan_cs.xlsx';
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                    $filePath = storage_path("app/public/$fileName");
                    $writer->save($filePath);
        
                    return response()->download($filePath)->deleteFileAfterSend();
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanPerolehans::route('/'),
            'create' => Pages\CreateLaporanPerolehan::route('/create'),
            'edit' => Pages\EditLaporanPerolehan::route('/{record}/edit'),
        ];
    }
}
