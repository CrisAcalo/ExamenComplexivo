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
        Schema::create('carreras_periodos_has_rubrica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_periodo_id')
                ->constrained('carreras_periodos')
                ->onDelete('cascade');
            $table->foreignId('rubrica_id')
                ->constrained('rubricas')
                ->onDelete('cascade');
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
        Schema::dropIfExists('carreras_periodos_has_rubrica');
    }
};
