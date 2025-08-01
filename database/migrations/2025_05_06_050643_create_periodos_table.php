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
    {//codigo_periodo, fecha_inicio, fecha_fin
        Schema::create('periodos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_periodo', 20)->unique(); //Ejemplo: 20250102
            $table->string('descripcion', 20)->unique(); //Ejemplo: MAY-SEP25
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodos');
    }
};
