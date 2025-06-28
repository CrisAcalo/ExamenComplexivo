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
        Schema::create('asignacion_calificador_comp_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_plan_id')
                  ->constrained('items_plan_evaluacion')
                  ->onDelete('cascade');
            $table->foreignId('componente_rubrica_id') // El componente de la plantilla de rúbrica
                  ->constrained('componentes_rubrica') // Asumiendo que tu tabla es 'componentes_rubrica'
                  ->onDelete('cascade');

            $table->enum('calificado_por', [
                'MIEMBROS_TRIBUNAL',
                'CALIFICADORES_GENERALES', // Se refiere al grupo único de calificadores generales del carrera_periodo
                'DIRECTOR_CARRERA',
                'DOCENTE_APOYO'
            ]);
            // No necesitamos 'grupo_calificador_general_id' si asumimos un solo conjunto de calificadores generales por carrera_periodo

            $table->timestamps();

            $table->unique(['item_plan_id', 'componente_rubrica_id'], 'item_plan_componente_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asignacion_calificador_comp_plans');
    }
};
