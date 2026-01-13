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
        Schema::table('users', function (Blueprint $table) {
            // Agregar departamento_id para vincular docentes a departamentos
            // Nullable porque no todos los users son docentes (Super Admin, Administrador, etc.)
            $table->foreignId('departamento_id')
                  ->nullable()
                  ->after('password')
                  ->constrained('departamentos')
                  ->onDelete('set null'); // Si se elimina el departamento, el docente queda sin departamento

            // Índice para mejorar rendimiento de consultas
            $table->index('departamento_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['departamento_id']);
            $table->dropIndex(['departamento_id']);
            $table->dropColumn('departamento_id');
        });
    }
};
