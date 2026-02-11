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
        Schema::create('donatur_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('donatur_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 50); // created, updated, viewed, note_added, etc
            $table->string('description')->nullable();
            $table->json('metadata')->nullable(); // Additional data like old/new values
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('donatur_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donatur_activity_logs');
    }
};
