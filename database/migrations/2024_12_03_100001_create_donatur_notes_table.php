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
        Schema::create('donatur_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('donatur_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('note');
            $table->timestamps();
            
            $table->index('donatur_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donatur_notes');
    }
};
