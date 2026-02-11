<?php

namespace App\Filament\Pages;

use Filament\Widgets;
use App\Models\Kwitansiv2;
use App\Models\LaporanPerolehan; // Pastikan model ini sesuai dengan data donatur
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use App\Models\CustomerService;
use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\jmlkwt;
use App\Filament\Widgets\PerolehanMonthly;
use App\Filament\Widgets\lapwid;
use App\Filament\Widgets\progwid;
use App\Filament\Widgets\CSPerformanceWidget;
use App\Filament\Widgets\DailyPerformanceWidget;
use App\Filament\Widgets\FridayPerformanceWidget;
use App\Filament\Widgets\CSFridayPerformanceWidget;
use App\Filament\Widgets\TimFridayPerformanceWidget;
use App\Filament\Widgets\TimPerformanceWidget;
use App\Filament\Widgets\DonasiMonthly;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function getWidgets(): array
    {
        return [
            lapwid::class,
            progwid::class,
            PerolehanMonthly::class,
            DonasiMonthly::class,
            DailyPerformanceWidget::class,
            FridayPerformanceWidget::class,
            TimFridayPerformanceWidget::class,
            TimPerformanceWidget::class,
            CSPerformanceWidget::class,
            CSFridayPerformanceWidget::class,
            //jmlkwt::class, // Pastikan Widget jmlkwt sudah ada
        ];  
    }

    public function filtersForm(Form $form): Form
{
    return $form->schema([
        Section::make("LAZ Al-Bahjah Barat Satu Dashboard")
            ->schema([
                // Filter untuk Perolehan Jam
                Select::make('perolehan_jam')
                    ->label('Perolehan Jam')
                    ->options([
                        '08:00-09:00 WIB' => '08:00-09:00 WIB',
                        '09:00-10:00 WIB' => '09:00-10:00 WIB',
                        '10:00-11:00 WIB' => '10:00-11:00 WIB',
                        '11:00-12:00 WIB' => '11:00-12:00 WIB',
                        '12:00-13:00 WIB' => '12:00-13:00 WIB',
                        '13:00-14:00 WIB' => '13:00-14:00 WIB',
                        '14:00-15:00 WIB' => '14:00-15:00 WIB',
                        '15:00-16:00 WIB' => '15:00-16:00 WIB',
                        '16:00-17:00 WIB' => '16:00-17:00 WIB',
                        '17:00-24:00 WIB' => '17:00-24:00 WIB',
                    ])
                    ->placeholder('Pilih Perolehan Jam'), // Placeholder jika belum dipilih
                
                // Filter Nama CS dengan opsi tetap
                Select::make('nama_cs')
                 ->label('Nama CS')
                        ->options(CustomerService::query()->pluck('name', 'name'))
                        ->required()
                        ->placeholder('Pilih Nama CS'), // Placeholder jika belum dipilih
                
                // Filter Tim dengan opsi tetap
                Select::make('tim')
                        ->label('Tim')
                        ->options(CustomerService::query()->pluck('team', 'team'))
                        ->required()
                    ->placeholder('Pilih Tim'), // Placeholder jika belum dipilih
                
                // DatePicker untuk created_at (Dari Tanggal)
                DatePicker::make('created_at')
                    ->label('Dari Tanggal')
                    ->placeholder('dd/mm/yyyy'),

                // DatePicker untuk updated_at (Sampai Tanggal)
                DatePicker::make('updated_at')
                    ->label('Sampai Tanggal')
                    ->placeholder('dd/mm/yyyy'),
                    ])
                    ->columns(5),
            Section::make("FILTER PROGRAM")
            ->schema([
                // Filter untuk Perolehan Jam
                Select::make('program_utama')
                    ->label('Pilih Program utama')
                    ->options([
                        'Sedekah Subuh' => 'Sedekah Subuh',
                        'Sedekah Jumat' => 'Sedekah Jumat',
                        'Infaq Makan Santri & Pejuang' => 'Infaq Makan Santri & Pejuang',
                    ]),
                    //->placeholder('Hasil Dari'), // Placeholder jika belum dipilih
                    Select::make('hasil_dari')
                    ->label('Hasil Dari')
                    ->options([
                        'Cross Selling' => 'Cross Selling',
                        'Infaq' => 'Infaq',
                        'Wakaf' => 'Wakaf',
                        'Produk' => 'Produk',
                        'Zakat' => 'Zakat',
                        'Platform' => 'Platform',
                    ]),
                
                // Filter Nama CS dengan opsi tetap
                Select::make('prg_cross_selling')
                    ->label('Cross Selling')
                    ->options([
                                        'Produk' => 'Produk',
                                        'Program AB Barat' => 'Program AB Barat',
                                        'Program Cabang' => 'Program Cabang',
                                        'Wakaf AB Barat' => 'Wakaf AB Barat',
                                        'Program Ramadhan' => 'Program Ramadhan',
                                        'Wakaf Cabang' => 'Wakaf Cabang',
                                        'Umroh/haji' => 'Umroh/haji',
                                        'Qurban' => 'Qurban',
                                        'Wakaf Quran' => 'Wakaf Quran',
                                        'Palestina' => 'Palestina',
                                        //'TOBETOSI'=>'TOBETOSI',
                                        'Dana Sosial'=>'Dana Sosial',
                                    ]), // Placeholder jika belum dipilih
                // Filter Tim dengan opsi tetap
                Select::make('zakat')
                    ->label('Zakat')
                    ->options([
                        'Zakat Maal' => 'Zakat Maal',
                        'Zakat Fitrah' => 'Zakat Fitrah',
                        'Zakat Penghasilan' => 'Zakat Penghasilan',
                        'Zakat Fidyah' => 'Zakat Fidyah',
                        'Dana Subhat' => 'Dana Subhat',
                    ]), // Placeholder jika belum dipilih
                
                // DatePicker untuk created_at (Dari Tanggal)
                Select::make('nama_produk')
                    ->label('Produk')
                    ->options([
                        'Buku Buya' => 'Buku Buya',
                        'Rendang Santri' => 'Rendang Santri',
                        'Al-Quran' => 'Al-Quran',
                        'Madu ANH' => 'Madu ANH',
                        'Produk MH' => 'Produk MH',
                        'Wakaf Tasbih' => 'Wakaf Tasbih',
                        'Infaq Qurma' => 'Infaq Qurma',
                        'Mukena' => 'Mukena',
                    ]),
            ])
            ->columns(5)
            ->extraAttributes(['class' => 'bg-blue-500 text-white p-4 rounded-lg']), // Mengatur tata letak menjadi 5 kolom
    ]);
}


    protected static ?string $navigationIcon = 'heroicon-o-document-text';
}
