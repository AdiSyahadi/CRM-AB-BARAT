<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DataPegawai extends Model
{
    use HasFactory;

    // Sesuaikan nama tabel jika tidak mengikuti penamaan plural Laravel
    protected $table = 'data_pegawai';

    // Tentukan primary key jika bukan 'id' (default Laravel adalah 'id')
    protected $primaryKey = 'id_pegawai';

    // Jika kamu menggunakan auto-incrementing ID
    public $incrementing = true;

    // Jika id bertipe integer, cukup gunakan ini:
    protected $keyType = 'int';

    // Timestamps (karena kamu sudah punya created_at & updated_at)
    public $timestamps = true;

    // Kolom yang bisa diisi via mass assignment
    protected $fillable = [
        'nama_pegawai',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_telepon',
        'id_jabatan',
        'tanggal_masuk',
    ];

    // Casting atribut (misalnya untuk tanggal)
    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}