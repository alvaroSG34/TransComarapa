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
            $table->string('transaction_id', 100)->nullable()->after('qr_base64');
            $table->string('payment_method_transaction_id', 100)->nullable()->after('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pago_ventas', function (Blueprint $table) {
            $table->dropColumn(['transaction_id', 'payment_method_transaction_id']);
        });
    }
};
