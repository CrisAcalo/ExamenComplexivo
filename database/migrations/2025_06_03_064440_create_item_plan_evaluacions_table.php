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
        Schema::create('item_plan_evaluacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_evaluacion_id')
                ->constrained('planes_evaluacion')
                ->onDelete('cascade');
            $table->string('nombre_item');
            $table->enum('tipo_item', ['NOTA_DIRECTA', 'RUBRICA_TABULAR']);
            $table->decimal('ponderacion_global', 5, 2); // ej. 50.00 para 50%
            $table->foreignId('rubrica_plantilla_id')
                ->nullable()
                ->constrained('rubricas')
                ->onDelete('restrict');
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_plan_evaluacions');
    }
};
