<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tribunal_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tribunal_id')
                ->constrained('tribunales') // Asumiendo que tu tabla de tribunales se llama 'tribunales'
                ->onDelete('cascade');
            $table->foreignId('user_id')->nullable() // Quién hizo el cambio (puede ser null si es un cambio del sistema)
                ->constrained('users')
                ->onDelete('set null');
            $table->string('accion'); // Ej: "ACTUALIZACION_DATOS", "CAMBIO_MIEMBRO", "CALIFICACION_REGISTRADA"
            $table->text('descripcion'); // Ej: "Fecha cambiada de 2024-08-01 a 2024-08-02", "Presidente cambiado de DocenteA a DocenteB"
            $table->json('datos_antiguos')->nullable(); // Opcional: para guardar el estado anterior de los datos modificados
            $table->json('datos_nuevos')->nullable();   // Opcional: para guardar el nuevo estado
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tribunal_logs');
    }
};
