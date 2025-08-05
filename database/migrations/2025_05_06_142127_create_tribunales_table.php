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
    { //carrera_periodo_id, estudiante_id, fecha, hora_inicio, hora_fin
        Schema::create('tribunales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_periodo_id')
                ->constrained('carreras_periodos')
                ->onDelete('cascade');
            $table->foreignId('estudiante_id')
                ->nullable()
                ->constrained('estudiantes')
                ->onDelete('cascade');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->enum('estado', ['ABIERTO', 'CERRADO'])->default('ABIERTO');
            $table->boolean('es_plantilla')->default(false);
            $table->string('descripcion_plantilla')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tribunales');
    }
};
