<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Events\PagoVentaCreated;
use App\Events\PagoVentaUpdated;

class PagoVenta extends Model
{
    use HasFactory;

    protected $table = 'pago_ventas';

    protected $fillable = [
        'venta_id',
        'num_cuota',
        'fecha_pago',
        'monto',
        'metodo_pago',
        'qr_base64',
        'transaction_id',
        'payment_method_transaction_id',
        'estado_pago',
        'payment_intent_id',
        'payment_method_id',
        'stripe_session_id',
        'moneda',
        'monto_usd',
    ];

    // Disparar eventos automáticamente
    protected $dispatchesEvents = [
        'created' => PagoVentaCreated::class,
        'updated' => PagoVentaUpdated::class,
    ];

    protected function casts(): array
    {
        return [
            'fecha_pago' => 'datetime',
            'monto' => 'decimal:2',
            'num_cuota' => 'integer',
        ];
    }

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
