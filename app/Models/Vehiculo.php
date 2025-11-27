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

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'img_url_full',
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
        ];
    }

    // Accessor para obtener la URL completa de la imagen
    protected function imgUrlFull(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function () {
                if (!$this->img_url) {
                    return null;
                }
                
                // Si ya tiene la URL completa, retornarla tal cual
                if (str_starts_with($this->img_url, 'http://') || str_starts_with($this->img_url, 'https://')) {
                    return $this->img_url;
                }
                
                // Si tiene /storage/ al inicio, quitarlo (por compatibilidad con registros antiguos)
                $path = $this->img_url;
                if (str_starts_with($path, '/storage/')) {
                    $path = substr($path, 9); // Remover '/storage/'
                }
                
                // Construir la URL completa usando url() para forzar URL absoluta
                return url('storage/' . $path);
            }
        );
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
