<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Kwitansiv2 extends Model
{
        use HasFactory;

        // Menentukan kolom yang dapat diisi secara massal
        protected $fillable = [
            'nama_donatur',
            'tanggal',
            'jumlah_donasi',
            'jumlah_donasi2',
            'jumlah_donasi3',
            'jumlah_donasi4',
            'jumlah_donasi5',
            'nomor_kwitansi',
            'nama_donasi',
            'nama_donasi2',
            'nama_donasi3',
            'nama_donasi4',
            'nama_donasi5',
            'total_donasi',        // Kolom baru untuk total donasi
            'diserahkan',      // Kolom baru untuk nama penyerah
            'diterima',        // Kolom baru untuk nama penerima
            'alamat',
            'telepon',
            'terbilang',
        ];

        protected static function booted()
        {
            static::created(function ($kwitansi) {
                // Ubah string 'tanggal' menjadi objek Carbon untuk mendapatkan format tanggal yang benar
                $tanggal = Carbon::createFromFormat('Y-m-d', $kwitansi->tanggal);
                $kwitansi->nomor_kwitansi = '' . $tanggal->format('y') . '-' . $kwitansi->id;
                $kwitansi->save();  // Simpan perubahan setelah nomor kwitansi diisi
            });
        }
}