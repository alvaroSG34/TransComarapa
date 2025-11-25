<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visita extends Model
{
    protected $table = 'visitas';

    // Deshabilitar updated_at ya que la tabla solo tiene created_at
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'ruta',
        'ip_address',
        'user_agent',
        'usuario_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relación con Usuario (opcional, si está autenticado)
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Obtener el contador de visitas para una ruta específica
     */
    public static function contarPorRuta(string $ruta): int
    {
        return static::where('ruta', $ruta)->count();
    }

    /**
     * Obtener el contador total de visitas
     */
    public static function contarTotal(): int
    {
        return static::count();
    }

    /**
     * Obtener visitas recientes de una ruta
     */
    public static function visitasRecientesPorRuta(string $ruta, int $limite = 10)
    {
        return static::where('ruta', $ruta)
            ->orderBy('created_at', 'desc')
            ->limit($limite)
            ->get();
    }
}
