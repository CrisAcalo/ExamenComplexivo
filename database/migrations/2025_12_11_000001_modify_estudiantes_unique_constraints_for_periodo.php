<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Modifica las restricciones unique de la tabla estudiantes para permitir
     * que un mismo estudiante pueda registrarse en diferentes periodos académicos.
     */
    public function up(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            // Eliminar las restricciones unique individuales existentes
            $table->dropUnique(['cedula']);
            $table->dropUnique(['correo']);
            $table->dropUnique(['username']);
            $table->dropUnique(['ID_estudiante']);
        });

        Schema::table('estudiantes', function (Blueprint $table) {
            // Agregar índices únicos compuestos que incluyen carrera_periodo_id
            // Esto permite que el mismo estudiante exista en diferentes periodos
            $table->unique(['cedula', 'carrera_periodo_id'], 'estudiantes_cedula_periodo_unique');
            $table->unique(['correo', 'carrera_periodo_id'], 'estudiantes_correo_periodo_unique');
            $table->unique(['username', 'carrera_periodo_id'], 'estudiantes_username_periodo_unique');
            $table->unique(['ID_estudiante', 'carrera_periodo_id'], 'estudiantes_id_periodo_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            // Eliminar los índices únicos compuestos
            $table->dropUnique('estudiantes_cedula_periodo_unique');
            $table->dropUnique('estudiantes_correo_periodo_unique');
            $table->dropUnique('estudiantes_username_periodo_unique');
            $table->dropUnique('estudiantes_id_periodo_unique');
        });

        Schema::table('estudiantes', function (Blueprint $table) {
            // Restaurar las restricciones unique individuales
            $table->unique('cedula');
            $table->unique('correo');
            $table->unique('username');
            $table->unique('ID_estudiante');
        });
    }
};
