<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculos';

    protected $fillable = [
        'placa',
        'marca',
        'modelo',
        'anio',
        'color',
        'tipo',
        'estado',
        'img_url',
        'conductor_id',
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
        ];
    }

    // Relaciones
    public function conductor()
    {
        return $this->belongsTo(Usuario::class, 'conductor_id');
    }

    public function viajes()
    {
        return $this->hasMany(Viaje::class, 'vehiculo_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'vehiculo_id');
    }
}
