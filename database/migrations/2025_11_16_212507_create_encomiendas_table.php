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
        Schema::create('encomiendas', function (Blueprint $table) {
            $table->foreignId('venta_id')->primary()->constrained('ventas')->onUpdate('cascade')->cascadeOnDelete();
            $table->foreignId('ruta_id')->constrained('rutas')->onUpdate('cascade')->restrictOnDelete();
            $table->decimal('peso', 10, 2);
            $table->text('descripcion')->nullable();
            $table->string('nombre_destinatario', 150);
            $table->text('img_url')->nullable();
            $table->string('modalidad_pago', 20)->default('origen'); // origen, mixto, destino
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encomiendas');
    }
};
