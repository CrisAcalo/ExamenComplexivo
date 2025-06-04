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
        Schema::create('planes_evaluacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_periodo_id')
                ->constrained('carreras_periodos')
                ->onDelete('cascade');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->unique(['carrera_periodo_id'], 'plan_carrera_periodo_unique'); // Solo un plan por carrera_periodo
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('planes_evaluacion');
    }
};
