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
            // $table->foreignId('miembro_tribunal_id')->constrained('miembros_tribunales')->onDelete('cascade'); // SE VA

            $table->foreignId('user_id'); // Quién calificó (puede ser miembro, director, apoyo, calificador general)
            //->constrained('users') // No añadir constrained si no siempre es un User de la tabla users (ver nota abajo)
            //->onDelete('cascade');

            $table->foreignId('tribunal_id') // Para qué tribunal/estudiante es esta calificación
                ->constrained('tribunales') // Asumiendo que tu tabla es 'tribunales'
                ->onDelete('cascade');

            $table->foreignId('item_plan_evaluacion_id') // El ítem específico del plan que se está calificando
                ->constrained('items_plan_evaluacion')
                ->onDelete('cascade');

            $table->foreignId('criterio_id')->nullable() // Solo para RUBRICA_TABULAR
                ->constrained('criterios_componente')
                ->onDelete('cascade');
            $table->foreignId('calificacion_criterio_id')->nullable() // Solo para RUBRICA_TABULAR
                ->constrained('calificaciones_criterio')
                ->onDelete('cascade');

            $table->decimal('nota_obtenida_directa', 5, 2)->nullable(); // Solo para NOTA_DIRECTA
            $table->text('observacion')->nullable(); // Observación general del ítem o específica del criterio
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Índices para mejorar rendimiento de consultas
            $table->index(['user_id', 'tribunal_id', 'item_plan_evaluacion_id'], 'calificacion_unique_query_index');
            $table->index(['tribunal_id', 'item_plan_evaluacion_id', 'criterio_id'], 'calificacion_criterio_query_index');
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
