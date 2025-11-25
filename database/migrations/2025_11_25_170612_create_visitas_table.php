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
        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->string('ruta', 255); // Ruta visitada (ej: /dashboard, /rutas)
            $table->string('ip_address', 45)->nullable(); // IPv4 o IPv6
            $table->text('user_agent')->nullable(); // Navegador/dispositivo
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onUpdate('cascade')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index('ruta'); // Índice para búsquedas rápidas por ruta
            $table->index('created_at'); // Índice para consultas por fecha
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitas');
    }
};
