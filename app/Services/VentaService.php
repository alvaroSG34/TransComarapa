<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Boleto;
use App\Models\Encomienda;
use App\Repositories\Contracts\VentaRepositoryInterface;
use Illuminate\Support\Facades\DB;

class VentaService
{
    protected $ventaRepository;

    public function __construct(VentaRepositoryInterface $ventaRepository)
    {
        $this->ventaRepository = $ventaRepository;
    }

    /**
     * Crear una venta con boletos
     */
    public function crearVentaBoleto(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            $venta = $this->ventaRepository->create([
                'fecha' => $data['fecha'],
                'monto_total' => $data['monto_total'],
                'tipo' => 'Boleto',
                'estado_pago' => 'Pendiente',
                'usuario_id' => $data['usuario_id'],
                'vehiculo_id' => $data['vehiculo_id'],
            ]);

            // Crear boletos asociados
            if (isset($data['boletos'])) {
                foreach ($data['boletos'] as $boletoData) {
                    Boleto::create([
                        'asiento' => $boletoData['asiento'],
                        'venta_id' => $venta->id,
                        'ruta_id' => $boletoData['ruta_id'],
                        'viaje_id' => $boletoData['viaje_id'],
                    ]);
                }
            }

            return $venta->load('boletos');
        });
    }

    /**
     * Crear una venta con encomienda
     */
    public function crearVentaEncomienda(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            $venta = $this->ventaRepository->create([
                'fecha' => $data['fecha'],
                'monto_total' => $data['monto_total'],
                'tipo' => 'Encomienda',
                'estado_pago' => 'Pendiente',
                'usuario_id' => $data['usuario_id'],
                'vehiculo_id' => $data['vehiculo_id'],
            ]);

            // Crear encomienda asociada
            Encomienda::create([
                'venta_id' => $venta->id,
                'ruta_id' => $data['encomienda']['ruta_id'],
                'peso' => $data['encomienda']['peso'],
                'descripcion' => $data['encomienda']['descripcion'] ?? null,
                'nombre_destinatario' => $data['encomienda']['nombre_destinatario'],
                'img_url' => $data['encomienda']['img_url'] ?? null,
                'modalidad_pago' => $data['encomienda']['modalidad_pago'] ?? 'origen',
            ]);

            return $venta->load('encomienda');
        });
    }

    /**
     * Obtener detalles de una venta
     */
    public function obtenerVenta(int $ventaId): ?Venta
    {
        return $this->ventaRepository->findWithRelations($ventaId);
    }

    /**
     * Cancelar una venta
     */
    public function cancelarVenta(int $ventaId): bool
    {
        return DB::transaction(function () use ($ventaId) {
            $result = $this->ventaRepository->update($ventaId, ['estado_pago' => 'anulado']);
            return $result !== null;
        });
    }
}
