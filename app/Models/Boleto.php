<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boleto extends Model
{
    use HasFactory;

    protected $table = 'boletos';

    protected $fillable = [
        'asiento',
        'venta_id',
        'ruta_id',
    ];

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
