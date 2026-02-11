<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Donatur extends Model
{
    use HasFactory;
    protected $table = 'donaturs';

    // Field yang bisa diisi (fillable)
    protected $fillable = [
        'id',               // Tambahan id
        'nama_cs',          // Tambahan nama_cs
        'did',              // Tambahan did
        'kat_donatur',
        'kode_donatur',
        'kode_negara',
        'tanggal_registrasi',
        'no_hp',            // Sudah sesuai
        'followup_wa', 
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
        'created_at',       // Tambahan created_at
        'updated_at',       // Tambahan updated_at
    ];    
        

    // Mengisi kolom 'did' otomatis
    protected static function booted()
    {
        static::created(function ($donatur) {
            // Mengubah 'tanggal_registrasi' menjadi objek Carbon
            $tanggal = Carbon::createFromFormat('Y-m-d', $donatur->tanggal_registrasi);
        
            // Menggabungkan id, kategori_donatur, dan tanggal untuk membuat 'did'
            $donatur->did = $donatur->kode_donatur . '' . $tanggal->format('dmY') . '' . $donatur->id;
        
            // Menyimpan perubahan tanpa memicu event lain
            $donatur->saveQuietly();
        });
        
    }
    
    public function laporans()
    {
        return $this->hasMany(LaporanPerolehan::class, 'no_hp', 'no_hp');
    }
}
