<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DaftarDonatur extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan
    protected $table = 'daftardonaturs';

    // Field yang bisa diisi (fillable)
    protected $fillable = [
        'kat_donatur',
        'kode_negara',
        'tanggal_registrasi',
        'no_hp',
        'followup_wa',  // Disesuaikan dengan perubahan nama kolom
        'nama_donatur',
        'nama_panggilan',
        'jenis_kelamin',
        'email',
        'alamat',
        'sosmed_account',
        'program',
        'channel',
        'fundraiser',
        'keterangan',
    ];

    // Mengisi kolom 'did' otomatis
    protected static function booted()
    {
        static::created(function ($donatur) {
            // Mengubah 'tanggal_registrasi' menjadi objek Carbon
            $tanggal = Carbon::createFromFormat('Y-m-d', $donatur->tanggal_registrasi);

            // Menggabungkan id, segmentasi_donatur, dan tanggal untuk membuat 'did'
            $donatur->did = $donatur->id . '-' . $donatur->segmentasi_donatur . '-' . $tanggal->format('Ymd');

            // Menyimpan perubahan tanpa memicu event lain
            $donatur->saveQuietly();
        });
    }
}
