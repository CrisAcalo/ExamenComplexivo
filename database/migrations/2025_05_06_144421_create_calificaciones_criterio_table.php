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
    { //criterio_id, nombre, descripcion, valor
        Schema::create('calificaciones_criterio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criterio_id')
                ->constrained('criterios_componente')
                ->onDelete('cascade');
            $table->string('nombre', 100);
            $table->decimal('valor', 5, 2);
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificaciones_criterio');
    }
};
