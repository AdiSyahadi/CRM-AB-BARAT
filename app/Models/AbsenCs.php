<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsenCs extends Model
{
    protected $table = 'absen_cs'; // Sesuaikan jika nama tabel berbeda

    protected $fillable = [
        'nama_cs',
        'status_kehadiran',
        'tanggal',
        'jam',
        'tipe_absen',
        'foto',
        'lokasi',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam' => 'datetime:H:i',
    ];

    // Set default tanggal & jam saat membuat absen baru jika tidak diberikan
    protected static function booted()
    {
        static::creating(function ($absen) {
            if (empty($absen->tanggal)) {
                $absen->tanggal = now()->toDateString();
            }

            if (empty($absen->jam)) {
                $absen->jam = now()->format('H:i:s');
            }
        });
    }
}
