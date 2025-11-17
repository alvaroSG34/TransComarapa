<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'fecha',
        'monto_total',
        'tipo',
        'estado_pago',
        'usuario_id',
        'vehiculo_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'monto_total' => 'decimal:2',
        ];
    }

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    public function boletos()
    {
        return $this->hasMany(Boleto::class, 'venta_id');
    }

    public function encomienda()
    {
        return $this->hasOne(Encomienda::class, 'venta_id');
    }

    public function pagos()
    {
        return $this->hasMany(PagoVenta::class, 'venta_id');
    }
}
