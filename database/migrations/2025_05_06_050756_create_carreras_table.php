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
    {//codigo_carrera, nombre, departamento, sede
        Schema::create('carreras', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_carrera', 10)->unique();
            $table->string('nombre', 100);
            //departamento_id desde tabla departamentos
            $table->unsignedBigInteger('departamento_id');
            $table->foreign('departamento_id')
            ->references('id')
            ->on('departamentos')
            ->onDelete('cascade');
            //modalidad
            $table->enum('modalidad', ['PRESENCIAL', 'EN LÍNEA'])->default('PRESENCIAL');
            $table->string('sede', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carreras');
    }
};
