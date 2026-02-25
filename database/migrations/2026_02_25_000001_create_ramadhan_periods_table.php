<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ramadhan_periods', function (Blueprint $table) {
            $table->id();
            $table->integer('hijri_year')->unique()->comment('Tahun Hijriah, misal 1445');
            $table->string('label')->comment('Label tampilan, misal Ramadhan 1445H / 2024M');
            $table->date('start_date')->comment('Tanggal awal Ramadhan (Masehi)');
            $table->date('end_date')->comment('Tanggal akhir Ramadhan (Masehi)');
            $table->bigInteger('target')->default(0)->comment('Target perolehan dalam Rupiah');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ramadhan_periods');
    }
};
