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
        Schema::create('calificador_general_carrera_periodos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_periodo_id')
                ->constrained('carreras_periodos')
                ->onDelete('cascade');
            $table->foreignId('user_id') // El docente que es calificador general
                ->constrained('users')
                ->onDelete('cascade');
            // Puedes añadir un campo 'rol_especifico' si un calificador general tiene diferentes roles o especialidades.
            // $table->string('especialidad')->nullable();
            $table->timestamps();

            $table->unique(['carrera_periodo_id', 'user_id'], 'cg_carrera_periodo_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('calificador_general_carrera_periodos'); // O el nombre correcto de tu tabla
        Schema::enableForeignKeyConstraints();
    }
};
