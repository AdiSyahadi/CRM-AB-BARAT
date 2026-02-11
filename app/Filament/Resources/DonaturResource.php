<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonaturResource\Pages;
use App\Filament\Resources\DonaturResource\RelationManagers;
use App\Models\Donatur;
use Filament\Forms;
use Filament\Forms\Form;
use App\Models\CustomerService;
use Filament\Tables\Columns\ToggleColumn;
//use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DonaturResource extends Resource
{
    protected static ?string $model = Donatur::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'DATABASE DONATUR';
    
    // Redirect ke CRM yang baru
    public static function getUrl(string $name = 'index', array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?\Illuminate\Database\Eloquent\Model $tenant = null): string
    {
        if ($name === 'index') {
            return route('donatur.index');
        }
        return parent::getUrl($name, $parameters, $isAbsolute, $panel, $tenant);
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('nama_cs')
                        ->label('Nama CS')
                        ->options(CustomerService::query()->pluck('name', 'name'))
                        ->required(),
                Forms\Components\Select::make('kat_donatur')
                            ->label('Kategori Donatur')
                            ->options([
                                'Retail' => 'Retail',
                                'Corporate' => 'Corporate',
                                'Community' => 'Community',
                            ])
                            ->required(),
                Forms\Components\Select::make('kode_donatur')
                            ->label('Kode Donatur')
                            ->options([
                                '1' => '1',
                                '2' => '2',
                                '3' => '3',
                            ])
                            ->required(),
                Forms\Components\Select::make('kode_negara')
                    ->label('Kode Negara')
                    ->options([
                        '+62' => 'ID +62',
                        '+966' => 'ARB +966',
                        '+852' => 'HK +852',
                        '+886' => 'TWN +886',
                         '+65' => 'SG +65',
                        '+60' => 'MY +60',
                        // Tambahkan kode negara lainnya di sini
                    ]),
                Forms\Components\TextInput::make('no_hp')
                    ->label('Nomor HP')
                    ->required(),
                    //->unique(),
                Forms\Components\DatePicker::make('tanggal_registrasi')
                    ->label('Tanggal Registrasi')
                    ->required(),
                Forms\Components\TextInput::make('nama_donatur')
                    ->label('Nama Donatur')
                    ->required(),
                Forms\Components\TextInput::make('nama_panggilan')
                    ->label('Nama Panggilan'),
                    //->required(),
                Forms\Components\Select::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'Laki-Laki' => 'Laki-laki',
                        'Perempuan' => 'Perempuan',
                    ]),
                    //->required(),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email(),
                    //->required(),
                Forms\Components\TextInput::make('sosmed_account')
                    ->label('Akun Sosial Media'),
                    //->required(),
                Forms\Components\Textarea::make('alamat')
                    ->label('Alamat'),
                    //->required(),
                Forms\Components\TextInput::make('program')
                    ->label('Program'),
                    //->required(),
                Forms\Components\TextInput::make('channel')
                    ->label('Channel'),
                    //->required(),
                Forms\Components\TextInput::make('fundraiser')
                    ->label('Fundraiser'),
                    //->required(),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan'),
                   //->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->recordUrl(null) // 🔒 Menonaktifkan clickable row
            ->columns([
                Tables\Columns\TextColumn::make('did')
                    ->label('DID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_cs')
                    ->label('Nama CS')
                    ->sortable()
                    ->searchable(),
                //Tables\Columns\TextColumn::make('kat_donatur')
                    //->label('Kategori Donatur')
                    //->sortable()
                    //->searchable(),
                //Tables\Columns\TextColumn::make('kode_negara')
                    //->label('Kode Negara')
                    //->sortable()
                    //->searchable(),
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('Nomor HP')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_registrasi')
                    ->label('Tanggal Registrasi')
                    ->sortable()
                    ->date(),
                Tables\Columns\TextColumn::make('nama_donatur')
                    ->label('Nama Donatur')
                    ->sortable()
                    ->searchable(),
                //Tables\Columns\TextColumn::make('nama_panggilan')
                    //->label('Nama Panggilan')
                    //->sortable()
                    //->searchable(),
                //Tables\Columns\TextColumn::make('jenis_kelamin')
                    //->label('Jenis Kelamin')
                    //->sortable(),
                //Tables\Columns\TextColumn::make('email')
                    //->label('Email')
                    //->sortable()
                    //->searchable(),
                //Tables\Columns\TextColumn::make('sosmed_account')
                    //->label('Akun Sosmed')
                    //->sortable()
                    //->searchable(),
                //Tables\Columns\TextColumn::make('alamat')
                    //->label('Alamat')
                    //->sortable()
                    //->searchable(),
                //Tables\Columns\TextColumn::make('program')
                    //->label('Program')
                    //->sortable()
                    //->searchable(),
                //Tables\Columns\TextColumn::make('channel')
                    //->label('Channel')
                    //->sortable()
                    //->searchable(),
                //Tables\Columns\TextColumn::make('fundraiser')
                    //->label('Fundraiser')
                    //->sortable()
                    //->searchable(),
                //Tables\Columns\TextColumn::make('keterangan')
                    //->label('Keterangan')
                    //->sortable()
                    //->searchable(),
                //Tables\Columns\TextColumn::make('donasi')
                    //->label('Status Donasi')
                    //->sortable(),
                //Tables\Columns\TextColumn::make('followup_wa')
                    //->label('Follow-up WA')
                    //->sortable()
                    //->getStateUsing(fn ($record) => $record->followup_wa ? 'Sudah' : 'Belum')
                    //->colors([
                      //  'primary' => 'Belum',
                        //'success' => 'Sudah',
                   // ]),
                //ToggleColumn::make('followup_wa')
                  //  ->label('Konfirmasi Follow Up')
                    //->onColor('success')
                    //->offColor('danger')
                    //->action(function ($record, $state) {
                        // Mengubah nilai field followup_wa sesuai status toggle
                      //  $record->followup_wa = $state ? 1 : 0;
                    //    $record->save();
                    //})
                    //->disabled(fn ($record) => $record->someCondition),
            ])
            ->filters([
                
                Tables\Filters\Filter::make('Tidak Donasi Dalam_2 Minggu')
                        ->query(fn ($query) => $query->whereDoesntHave('laporans', function ($subQuery) {
                            $subQuery->where('tanggal', '>=', now()->subWeeks(2));
                        })),
                Tables\Filters\Filter::make('Tidak Donasi Dalam_1 Bulan')
                        ->query(fn ($query) => $query->whereDoesntHave('laporans', function ($subQuery) {
                            $subQuery->where('tanggal', '>=', now()->subMonth(1));
                        })),
                Tables\Filters\Filter::make('Tidak Donasi Dalam_2 Bulan')
                        ->query(fn ($query) => $query->whereDoesntHave('laporans', function ($subQuery) {
                            $subQuery->where('tanggal', '>=', now()->subMonths(2));
                        })),
                    // 🔥 Filter baru: Tidak Donasi Dalam 1 Tahun
                Tables\Filters\Filter::make('Tidak Donasi Dalam_1 Tahun')
                    ->query(fn ($query) => $query->whereDoesntHave('laporans', function ($subQuery) {
                        $subQuery->where('tanggal', '>=', now()->subYear());
                    })),
                        
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Registrasi Donatur'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['start_date'])) {
                            $query->whereDate('tanggal_registrasi', '>=', $data['start_date']);
                        }
                        if (!empty($data['end_date'])) {
                            $query->whereDate('tanggal_registrasi', '<=', $data['end_date']);
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('open_whatsapp')
                        ->label('Chat')
                        ->icon('heroicon-o-clock')
                        ->url(fn ($record) => 'https://wa.me/' . $record->no_hp)
                        ->openUrlInNewTab()
                        ->color('info'),

                    // Action kedua untuk konfirmasi follow up
                    

             // Jika perlu membuat toggle non-aktif berdasarkan kondisi tertentu


            ])
                        
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDonaturs::route('/'),
            'create' => Pages\CreateDonatur::route('/create'),
            'edit' => Pages\EditDonatur::route('/{record}/edit'),
        ];
    }
}
