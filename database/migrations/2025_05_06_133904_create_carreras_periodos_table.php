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
    { //carrera_id, periodo_id, docente_apoyo_id, director_id
        Schema::create('carreras_periodos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_id')
                ->constrained('carreras')
                ->onDelete('cascade');
            $table->foreignId('periodo_id')
                ->constrained('periodos')
                ->onDelete('cascade');
            $table->foreignId('docente_apoyo_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('director_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('carreras_periodos');

        // Volver a habilitar las restricciones de clave foránea
        Schema::enableForeignKeyConstraints();
    }
};
