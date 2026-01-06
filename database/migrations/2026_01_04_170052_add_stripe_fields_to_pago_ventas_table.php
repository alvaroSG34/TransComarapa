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
        Schema::table('pago_ventas', function (Blueprint $table) {
            $table->string('payment_intent_id')->nullable()->after('qr_base64');
            $table->string('payment_method_id')->nullable()->after('payment_intent_id');
            $table->string('stripe_session_id')->nullable()->after('payment_method_id');
            $table->string('moneda')->default('BOB')->after('monto'); // BOB o USD
            $table->decimal('monto_usd', 10, 2)->nullable()->after('moneda'); // Monto convertido a USD para Stripe
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pago_ventas', function (Blueprint $table) {
            $table->dropColumn(['payment_intent_id', 'payment_method_id', 'stripe_session_id', 'moneda', 'monto_usd']);
        });
    }
};
