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
    ];

    // Relaciones
    public function boletos()
    {
        return $this->hasMany(Boleto::class, 'ruta_id');
    }

    public function encomiendas()
    {
        return $this->hasMany(Encomienda::class, 'ruta_id');
    }
}
