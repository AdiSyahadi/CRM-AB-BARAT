<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Patch 022: MySQL AFTER INSERT trigger pada tabel laporans
     * Auto-create kwitansi di level database, tidak tergantung aplikasi mana yang INSERT.
     */
    public function up(): void
    {
        // Drop trigger jika sudah ada (safety)
        DB::unprepared('DROP TRIGGER IF EXISTS trg_laporans_auto_kwitansi');

        DB::unprepared("
            CREATE TRIGGER trg_laporans_auto_kwitansi
            AFTER INSERT ON laporans
            FOR EACH ROW
            BEGIN
                DECLARE v_nama_donasi VARCHAR(255);
                DECLARE v_kwitansi_id INT;

                -- Hanya buat kwitansi jika ada nama donatur DAN nominal > 0
                IF NEW.nama_donatur IS NOT NULL
                   AND TRIM(NEW.nama_donatur) != ''
                   AND NEW.jml_perolehan IS NOT NULL
                   AND NEW.jml_perolehan > 0
                THEN
                    -- Tentukan nama_donasi dari field yang tersedia
                    SET v_nama_donasi = COALESCE(
                        NULLIF(TRIM(COALESCE(NEW.program_utama, '')), ''),
                        NULLIF(TRIM(COALESCE(NEW.program, '')), ''),
                        NULLIF(TRIM(COALESCE(NEW.nama_produk, '')), ''),
                        'Donasi Umum'
                    );

                    -- Insert kwitansi
                    INSERT INTO kwitansis (tanggal, nama_donatur, jumlah_donasi, nama_donasi, laporan_id, created_at, updated_at)
                    VALUES (NEW.tanggal, TRIM(NEW.nama_donatur), NEW.jml_perolehan, v_nama_donasi, NEW.id, NOW(), NOW());

                    -- Set nomor_kwitansi = YYYYmmdd + kwitansi_id
                    SET v_kwitansi_id = LAST_INSERT_ID();
                    UPDATE kwitansis
                       SET nomor_kwitansi = CONCAT(DATE_FORMAT(NEW.tanggal, '%Y%m%d'), v_kwitansi_id)
                     WHERE id = v_kwitansi_id;
                END IF;
            END
        ");
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_laporans_auto_kwitansi');
    }
};
