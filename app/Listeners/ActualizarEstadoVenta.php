<?php

namespace App\Listeners;

use App\Events\PagoVentaCreated;
use App\Events\PagoVentaUpdated;
use App\Repositories\Contracts\PagoVentaRepositoryInterface;
use App\Repositories\Contracts\VentaRepositoryInterface;

class ActualizarEstadoVenta
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
     * Handle the event.
     */
    public function handle(PagoVentaCreated|PagoVentaUpdated $event): void
    {
        $pagoVenta = $event->pagoVenta;
        
        // Calcular total pagado usando el repository
        $totalPagado = $this->pagoVentaRepository->getTotalPagadoByVenta($pagoVenta->venta_id);

        // Obtener venta
        $venta = $this->ventaRepository->find($pagoVenta->venta_id);
        
        if ($venta) {
            // Actualizar estado según el total pagado
            if ($totalPagado >= $venta->monto_total) {
                $this->ventaRepository->update($venta->id, ['estado_pago' => 'Pagado']);
            } else {
                $this->ventaRepository->update($venta->id, ['estado_pago' => 'Pendiente']);
            }
        }
    }
}
