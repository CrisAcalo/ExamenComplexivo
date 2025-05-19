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
    {//tribunal_id, user_id, status
        Schema::create('miembros_tribunales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tribunal_id')
                ->constrained('tribunales')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->enum('status', ['PRESIDENTE', 'INTEGRANTE1', 'INTEGRANTE2']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('miembros_tribunales');
    }
};
