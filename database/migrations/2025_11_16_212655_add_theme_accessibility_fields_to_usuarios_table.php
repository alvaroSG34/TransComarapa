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
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('tema_preferido', 20)->default('jovenes')->after('img_url'); // ninos, jovenes, adultos
            $table->string('modo_contraste', 20)->default('normal')->after('tema_preferido'); // normal, alto
            $table->string('tamano_fuente', 20)->default('mediano')->after('modo_contraste'); // pequeno, mediano, grande
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['tema_preferido', 'modo_contraste', 'tamano_fuente']);
        });
    }
};
