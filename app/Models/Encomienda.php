<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encomienda extends Model
{
    use HasFactory;

    protected $table = 'encomiendas';
    protected $primaryKey = 'venta_id';
    public $incrementing = false;

    protected $fillable = [
        'venta_id',
        'ruta_id',
        'viaje_id',
        'peso',
        'descripcion',
        'nombre_destinatario',
        'img_url',
        'modalidad_pago',
        'metodo_pago_destino',
        'monto_pagado_origen',
        'monto_pagado_destino',
    ];

    protected function casts(): array
    {
        return [
            'peso' => 'decimal:2',
        ];
    }

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }
}
