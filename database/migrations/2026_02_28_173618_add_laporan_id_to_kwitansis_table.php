<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop kolom jika ada dari attempt sebelumnya (tipe salah)
        if (Schema::hasColumn('kwitansis', 'laporan_id')) {
            Schema::table('kwitansis', function (Blueprint $table) {
                $table->dropColumn('laporan_id');
            });
        }

        Schema::table('kwitansis', function (Blueprint $table) {
            // laporans.id = int(11) signed, harus match
            $table->integer('laporan_id')->nullable()->after('nama_donasi');
            $table->foreign('laporan_id')->references('id')->on('laporans')->nullOnDelete();
            $table->index('laporan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kwitansis', function (Blueprint $table) {
            $table->dropForeign(['laporan_id']);
            $table->dropIndex(['laporan_id']);
            $table->dropColumn('laporan_id');
        });
    }
};
