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
    { //carrera_periodo_id, nombre, ponderacion
        Schema::create('componentes_rubrica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubrica_id')
                ->constrained('rubricas')
                ->onDelete('cascade');
            $table->string('nombre', 100);
            $table->decimal('ponderacion', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('componentes_rubrica');
    }
};
