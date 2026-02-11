<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kwitansi extends Model
{
    use HasFactory;
    protected $fillable = ['tanggal', 'nama_donatur', 'jumlah_donasi', 'nama_donasi', /* field lainnya */];

    // Event untuk mengisi nomor kwitansi setelah record disimpan
    protected static function booted()
    {
        static::created(function ($kwitansi) {
            $tanggal = \Carbon\Carbon::createFromFormat('Y-m-d', $kwitansi->tanggal);
            $kwitansi->nomor_kwitansi = '' . $tanggal->format('Ymd') . '' . $kwitansi->id;
            $kwitansi->save();  // Simpan perubahan setelah nomor kwitansi diisi
        });

    }
}
