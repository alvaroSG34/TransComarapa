<?php

namespace App\Repositories\Eloquent;

use App\Models\PagoVenta;
use App\Repositories\Contracts\PagoVentaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PagoVentaRepository implements PagoVentaRepositoryInterface
{
    protected $model;

    public function __construct(PagoVenta $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->with('venta')->get();
    }

    public function find(int $id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $pago = $this->find($id);
        
        if ($pago) {
            $pago->update($data);
            return $pago->fresh();
        }
        
        return null;
    }

    public function delete(int $id)
    {
        $pago = $this->find($id);
        
        if ($pago) {
            return $pago->delete();
        }
        
        return false;
    }

    public function findByVenta(int $ventaId): Collection
    {
        return $this->model->where('venta_id', $ventaId)
            ->orderBy('num_cuota')
            ->get();
    }

    public function findPendientesByVenta(int $ventaId): Collection
    {
        return $this->model->where('venta_id', $ventaId)
            ->where('estado_pago', 'Pendiente')
            ->orderBy('num_cuota')
            ->get();
    }

    public function findPagadosByVenta(int $ventaId): Collection
    {
        return $this->model->where('venta_id', $ventaId)
            ->where('estado_pago', 'Pagado')
            ->orderBy('num_cuota')
            ->get();
    }

    public function getTotalPagadoByVenta(int $ventaId): float
    {
        return (float) $this->model->where('venta_id', $ventaId)
            ->where('estado_pago', 'Pagado')
            ->sum('monto');
    }
}
