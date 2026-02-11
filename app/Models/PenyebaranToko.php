<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PenyebaranToko extends Model
{
    protected $table = 'penyebaran_toko';

    protected $fillable = [
        'tanggal_registrasi',
        'nama_cs',
        //'did',
        'nama_toko',
        'nama_donatur',
        'nomor_kencleng',
        'no_hp',       // did dihapus dari sini
        'alamat',
        'status',
        'foto_base64',
        'keterangan',
        'latitude',
        'longitude',
    ];
    // Mengisi kolom 'did' otomatis
   // protected static function booted()
    //{
      //  static::created(function ($donatur) {
            // Mengubah 'tanggal_registrasi' menjadi objek Carbon
        //    $tanggal = Carbon::createFromFormat('Y-m-d', $donatur->tanggal_registrasi);
        
            // Menggabungkan id, kategori_donatur, dan tanggal untuk membuat 'did'
          //  $donatur->did = $donatur->kode_donatur . '' . $tanggal->format('dmY') . '' . $donatur->id;
        
            // Menyimpan perubahan tanpa memicu event lain
            //$donatur->saveQuietly();
        //});
        
  //  }
}


