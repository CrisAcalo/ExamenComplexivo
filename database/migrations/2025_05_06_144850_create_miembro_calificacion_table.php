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
    { //criterio_id, calificacion_criterio_id, miembro_tribubal_id
        Schema::create('miembro_calificacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criterio_id')
                ->constrained('criterios_componente')
                ->onDelete('cascade');
            $table->foreignId('calificacion_criterio_id')
                ->constrained('calificaciones_criterio')
                ->onDelete('cascade');
            $table->foreignId('miembro_tribunal_id')
                ->constrained('miembros_tribunales')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('miembro_calificacion');
    }
};
