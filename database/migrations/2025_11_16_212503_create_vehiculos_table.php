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
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('placa', 15)->unique();
            $table->string('marca', 50);
            $table->string('modelo', 50);
            $table->smallInteger('anio')->nullable();
            $table->string('color', 30)->nullable();
            $table->string('tipo', 30)->nullable();
            $table->string('estado', 20)->nullable();
            $table->text('img_url')->nullable();
            $table->foreignId('conductor_id')->constrained('usuarios')->onUpdate('cascade')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
