<?php

namespace App\Services;

use App\Events\PagoVentaCreated;
use App\Events\PagoVentaUpdated;
use App\Models\PagoVenta;
use App\Models\Venta;
use App\Repositories\Contracts\PagoVentaRepositoryInterface;
use App\Repositories\Contracts\VentaRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PagoService
{
    protected $pagoVentaRepository;
    protected $ventaRepository;

    public function __construct(
        PagoVentaRepositoryInterface $pagoVentaRepository,
        VentaRepositoryInterface $ventaRepository
    ) {
        $this->pagoVentaRepository = $pagoVentaRepository;
        $this->ventaRepository = $ventaRepository;
    }

    /**
     * Crear un nuevo pago para una venta
     */
    public function crearPago(array $data): PagoVenta
    {
        return DB::transaction(function () use ($data) {
            $pago = $this->pagoVentaRepository->create([
                'venta_id' => $data['venta_id'],
                'num_cuota' => $data['num_cuota'],
                'fecha_pago' => $data['fecha_pago'] ?? now(),
                'monto' => $data['monto'],
                'metodo_pago' => $data['metodo_pago'],
                'qr_base64' => $data['qr_base64'] ?? null,
                'estado_pago' => $data['estado_pago'] ?? 'Pagado',
            ]);

            // Disparar evento para actualizar estado de venta
            event(new PagoVentaCreated($pago));

            return $pago;
        });
    }

    /**
     * Actualizar un pago existente
     */
    public function actualizarPago(int $pagoId, array $data): ?PagoVenta
    {
        return DB::transaction(function () use ($pagoId, $data) {
            $pago = $this->pagoVentaRepository->update($pagoId, $data);
            
            if ($pago) {
                // Disparar evento para recalcular estado de venta
                event(new PagoVentaUpdated($pago));
            }

            return $pago;
        });
    }

    /**
     * Obtener pagos de una venta
     */
    public function obtenerPagosVenta(int $ventaId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->pagoVentaRepository->findByVenta($ventaId);
    }

    /**
     * Generar plan de cuotas para una venta
     */
    public function generarPlanCuotas(Venta $venta, int $numCuotas, string $metodoPago): array
    {
        $montoCuota = round($venta->monto_total / $numCuotas, 2);
        $plan = [];

        for ($i = 1; $i <= $numCuotas; $i++) {
            // Ajustar última cuota para compensar redondeos
            $monto = ($i === $numCuotas) 
                ? $venta->monto_total - ($montoCuota * ($numCuotas - 1))
                : $montoCuota;

            $plan[] = [
                'venta_id' => $venta->id,
                'num_cuota' => $i,
                'monto' => $monto,
                'metodo_pago' => $metodoPago,
                'estado_pago' => 'Pendiente',
            ];
        }

        return $plan;
    }

    /**
     * Registrar pago completo (pago único)
     */
    public function registrarPagoCompleto(int $ventaId, array $data): PagoVenta
    {
        $venta = $this->ventaRepository->find($ventaId);

        if (!$venta) {
            throw new \Exception("Venta no encontrada");
        }

        return $this->crearPago([
            'venta_id' => $ventaId,
            'num_cuota' => 1,
            'monto' => $venta->monto_total,
            'metodo_pago' => $data['metodo_pago'],
            'qr_base64' => $data['qr_base64'] ?? null,
            'estado_pago' => 'Pagado',
            'fecha_pago' => now(),
        ]);
    }
}
