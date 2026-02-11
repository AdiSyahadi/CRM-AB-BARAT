<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kencleng extends Model
{   
    use HasFactory;
    protected $table = 'penyebaran_toko';
    protected $fillable = [
        'id',
        'tanggal_registrasi',
        'nama_cs',
        'nama_toko',
        'nama_donatur',
        'nomor_kencleng',
        'no_hp',
        'alamat',
    ];
}
