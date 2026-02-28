<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
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
        'nama_toko',
        'kode_negara',
        'no_hp',
        'followup_wa',
        'hasil_dari',
        'prg_cross_selling',
        'adsense',
        'e_commerce',
        'program_utama',
        'nama_produk',
        'zakat',
        'wakaf',
        'nama_platform',
        'jenis_konten',
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

        // Auto-create kwitansi jika laporan punya transaksi nyata
        static::created(function ($laporan) {
            try {
                // Rule: hanya jika ada nama donatur DAN nominal > 0
                $namaDonatur = trim($laporan->nama_donatur ?? '');
                $jumlahPerolehan = (float) ($laporan->jml_perolehan ?? 0);

                if ($namaDonatur === '' || $jumlahPerolehan <= 0) {
                    return; // Laporan kosong, skip
                }

                // Tentukan nama donasi dari field yang tersedia
                $namaDonasi = trim($laporan->program_utama ?? '')
                    ?: trim($laporan->program ?? '')
                    ?: trim($laporan->nama_produk ?? '')
                    ?: 'Donasi Umum';

                \App\Models\Kwitansi::create([
                    'tanggal'       => $laporan->tanggal,
                    'nama_donatur'  => $namaDonatur,
                    'jumlah_donasi' => $jumlahPerolehan,
                    'nama_donasi'   => $namaDonasi,
                    'laporan_id'    => $laporan->id,
                ]);
            } catch (\Exception $e) {
                // Log error tapi jangan gagalkan proses input laporan
                Log::error('Auto-create kwitansi gagal untuk laporan #' . $laporan->id . ': ' . $e->getMessage());
            }
        });
    }
}