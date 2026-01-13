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
            $table->string('caso', 100)->nullable()->after('nombre_tribunal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tribunales', function (Blueprint $table) {
            $table->dropColumn('caso');
        });
    }
};
