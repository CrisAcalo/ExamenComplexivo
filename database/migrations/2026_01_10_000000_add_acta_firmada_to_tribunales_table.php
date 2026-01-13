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
    {
        Schema::table('tribunales', function (Blueprint $table) {
            $table->string('acta_firmada_path')->nullable()->after('nombre_tribunal');
            $table->unsignedBigInteger('acta_firmada_subida_por')->nullable()->after('acta_firmada_path');
            $table->timestamp('acta_firmada_fecha')->nullable()->after('acta_firmada_subida_por');

            $table->foreign('acta_firmada_subida_por')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tribunales', function (Blueprint $table) {
            $table->dropForeign(['acta_firmada_subida_por']);
            $table->dropColumn(['acta_firmada_path', 'acta_firmada_subida_por', 'acta_firmada_fecha']);
        });
    }
};
