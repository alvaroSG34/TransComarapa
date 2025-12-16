<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla de auditoría para Sistema Via Mail
     */
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('email_remitente')->index();
            $table->string('comando', 50)->index();
            $table->text('parametros')->nullable();
            $table->text('respuesta')->nullable();
            $table->string('estado', 20)->index(); // EXITOSO, ERROR
            $table->text('mensaje_error')->nullable();
            $table->integer('tiempo_ejecucion')->nullable()->comment('Tiempo en milisegundos');
            $table->timestamp('created_at')->useCurrent();
            
            // Índices para búsquedas frecuentes
            $table->index(['comando', 'estado']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
