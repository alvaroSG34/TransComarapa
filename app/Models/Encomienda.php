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
            'peso' => 'decimal:2',
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
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }
}
