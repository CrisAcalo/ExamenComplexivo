<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            // Agregar carrera_periodo_id para vincular estudiante a una carrera en un periodo específico
            $table->foreignId('carrera_periodo_id')
                  ->nullable() // Nullable temporalmente para permitir la migración
                  ->after('ID_estudiante')
                  ->constrained('carreras_periodos')
                  ->onDelete('cascade');

            // Índice para mejorar rendimiento de consultas
            $table->index('carrera_periodo_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropForeign(['carrera_periodo_id']);
            $table->dropIndex(['carrera_periodo_id']);
            $table->dropColumn('carrera_periodo_id');
        });
    }
};
