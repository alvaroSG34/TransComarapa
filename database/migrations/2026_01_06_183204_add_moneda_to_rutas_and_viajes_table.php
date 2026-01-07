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
        Schema::table('rutas', function (Blueprint $table) {
            $table->string('moneda', 3)->default('BOB')->after('distancia_km');
            $table->string('pais_operacion', 100)->default('Bolivia')->after('moneda');
        });

        Schema::table('viajes', function (Blueprint $table) {
            $table->string('moneda', 3)->default('BOB')->after('precio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rutas', function (Blueprint $table) {
            $table->dropColumn(['moneda', 'pais_operacion']);
        });

        Schema::table('viajes', function (Blueprint $table) {
            $table->dropColumn('moneda');
        });
    }
};
