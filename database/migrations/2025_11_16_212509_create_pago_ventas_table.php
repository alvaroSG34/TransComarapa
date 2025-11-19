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
        Schema::create('pago_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onUpdate('cascade')->cascadeOnDelete();
            $table->smallInteger('num_cuota');
            $table->timestamp('fecha_pago')->nullable();
            $table->decimal('monto', 10, 2);
            $table->string('metodo_pago', 30);
            $table->text('qr_base64')->nullable();
            $table->string('estado_pago', 20); // pagado, pendiente, anulado
            $table->timestamps();
            
            $table->unique(['venta_id', 'num_cuota']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago_ventas');
    }
};
