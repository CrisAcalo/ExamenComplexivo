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

            $table->foreignId('miembro_tribunal_id')
                ->constrained('miembros_tribunales')
                ->onDelete('cascade');

            // Para vincular directamente al ítem del plan de evaluación.
            // Esencial para notas directas y para agrupar calificaciones por ítem.
            $table->foreignId('item_plan_evaluacion_id')
                ->nullable() // Será null si la calificación es específica de un criterio de una rúbrica tabular
                ->constrained('items_plan_evaluacion')
                ->onDelete('cascade');

            // Para calificaciones basadas en rúbricas tabulares
            $table->foreignId('criterio_id')
                ->nullable() // Será null para notas directas o para observaciones generales de un item_plan_evaluacion
                ->constrained('criterios_componente')
                ->onDelete('cascade');

            $table->foreignId('calificacion_criterio_id')
                ->nullable() // Será null para notas directas o para observaciones generales de un item_plan_evaluacion
                ->constrained('calificaciones_criterio')
                ->onDelete('cascade'); // O 'set null' si una opción de calificación se borra pero quieres mantener el registro

            // Para notas directas (ej. Cuestionario)
            $table->decimal('nota_obtenida_directa', 5, 2)->nullable();

            // Observación general para el ítem o específica para el criterio
            $table->text('observacion')->nullable();

            $table->timestamps();

            // Opcional: Índices para mejorar el rendimiento de las búsquedas
            $table->index('miembro_tribunal_id');
            $table->index('item_plan_evaluacion_id');
            $table->index('criterio_id');
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
