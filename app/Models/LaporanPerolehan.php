<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LaporanPerolehan extends Model
{
    use HasFactory;

    protected $table = 'laporans';

    // Daftar field yang bisa diisi
    protected $fillable = [
         'id',
        'tanggal',
        'tim',
        'nama_cs',
        'perolehan_jam',
        'jml_database',
        'jml_perolehan',
        'nama_bank',
        'no_rek',
        'did',
        'nama_donatur',
        'kode_negara',
        'no_hp',
        'followup_wa',
        'hasil_dari',
        'prg_cross_selling',
        'adsense',
        'e_commerce',
        'nama_produk',
        'zakat',
        'nama_platform',
        'kat_donatur',
        'jenis_kelamin',
        'email',
        'sosmed_account',
        'alamat',
        'program',
        'channel',
        'fundraiser',
        'keterangan',
    ];

    /**
     * Boot the model and add validation before saving
     */
    protected static function booted()
    {
        static::saving(function ($model) {
            $requiredFields = ['tanggal', 'tim', 'nama_cs', 'perolehan_jam'];

            foreach ($requiredFields as $field) {
                if (is_null($model->{$field}) || $model->{$field} === '') {
                    throw ValidationException::withMessages([
                        $field => ['Field ini wajib diisi.']
                    ]);
                }
            }

            // Cek apakah semua field NULL (opsional)
            $allNull = true;
            foreach ($model->getAttributes() as $key => $value) {
                if (!in_array($key, ['id', 'created_at', 'updated_at']) && !is_null($value)) {
                    $allNull = false;
                    break;
                }
            }

            if ($allNull) {
                throw ValidationException::withMessages([
                    '*' => ['Tidak boleh menyimpan baris kosong. Minimal satu field harus diisi.']
                ]);
            }
        });
    }
}