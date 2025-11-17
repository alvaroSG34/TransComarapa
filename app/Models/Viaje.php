<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Viaje extends Model
{
    use HasFactory;

    protected $table = 'viajes';

    protected $fillable = [
        'ruta_id',
        'vehiculo_id',
        'fecha_salida',
        'fecha_llegada',
        'precio',
        'asientos_totales',
        'estado',
    ];

    protected $casts = [
        'fecha_salida' => 'datetime',
        'fecha_llegada' => 'datetime',
        'precio' => 'decimal:2',
        'asientos_totales' => 'integer',
    ];

    // Relaciones
    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function boletos()
    {
        return $this->hasMany(Boleto::class);
    }

    // Accesor: Asientos disponibles
    public function getAsientosDisponiblesAttribute()
    {
        return $this->asientos_totales - $this->boletos()->count();
    }

    // Accesor: ¿Tiene asientos disponibles?
    public function getTieneAsientosDisponiblesAttribute()
    {
        return $this->asientos_disponibles > 0;
    }

    // Accesor: ¿Se puede vender?
    public function getSePuedeVenderAttribute()
    {
        return $this->estado === 'programado' && $this->tiene_asientos_disponibles;
    }

    // Scope: Viajes disponibles para venta
    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'programado')
            ->where('fecha_salida', '>', now());
    }

    // Scope: Viajes por ruta
    public function scopePorRuta($query, $rutaId)
    {
        return $query->where('ruta_id', $rutaId);
    }

    // Scope: Viajes por fecha
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha_salida', $fecha);
    }
}
