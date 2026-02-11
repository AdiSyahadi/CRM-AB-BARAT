<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'status',
        'ubudiyah',
        'keterangan',
        'latitude',
        'longitude',
        'alamat',
        'jam',
        'tanggal',
        'foto', // jika nanti kamu menyimpan nama file foto
    ];
}
