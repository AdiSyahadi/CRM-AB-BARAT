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
        Schema::create('kwitansis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_donatur');
            $table->date('tanggal');
            $table->decimal('jumlah_donasi', 15, 2);
            $table->string('nomor_kwitansi')->unique();
            $table->string('nama_donasi');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kwitansis');
    }
};
