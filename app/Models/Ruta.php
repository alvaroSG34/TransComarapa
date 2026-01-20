<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    use HasFactory;

    protected $table = 'rutas';

    protected $fillable = [
        'origen',
        'destino',
        'nombre',
        'imagen',
        'moneda',
        'pais_operacion',
    ];

    // Relaciones
    public function viajes()
    {
        return $this->hasMany(Viaje::class, 'ruta_id');
    }

    public function boletos()
    {
        return $this->hasMany(Boleto::class, 'ruta_id');
    }

    public function encomiendas()
    {
        return $this->hasMany(Encomienda::class, 'ruta_id');
    }
}
