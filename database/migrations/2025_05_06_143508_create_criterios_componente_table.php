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
    {//componente_id, criterio
        Schema::create('criterios_componente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('componente_id')
                ->constrained('componentes_rubrica')
                ->onDelete('cascade');
            $table->string('nombre'); //la extension por defecto es varchar(255)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criterios_componente');
    }
};
