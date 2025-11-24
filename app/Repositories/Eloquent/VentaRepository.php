<?php

namespace App\Repositories\Eloquent;

use App\Models\Venta;
use App\Repositories\Contracts\VentaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class VentaRepository implements VentaRepositoryInterface
{
    protected $model;

    public function __construct(Venta $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->with(['usuario', 'vehiculo'])->get();
    }

    public function find(int $id)
    {
        return $this->model->find($id);
    }

    public function findWithRelations(int $id)
    {
        return $this->model->with([
            'usuario',
            'vehiculo.conductor',
            'boletos.ruta',
            'encomienda.ruta',
            'pagos'
        ])->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $venta = $this->find($id);
        
        if ($venta) {
            $venta->update($data);
            return $venta->fresh();
        }
        
        return null;
    }

    public function delete(int $id)
    {
        $venta = $this->find($id);
        
        if ($venta) {
            return $venta->delete();
        }
        
        return false;
    }

    public function findByUsuario(int $usuarioId): Collection
    {
        return $this->model->where('usuario_id', $usuarioId)
            ->with(['vehiculo', 'pagos'])
            ->orderBy('fecha', 'desc')
            ->get();
    }

    public function findByVehiculo(int $vehiculoId): Collection
    {
        return $this->model->where('vehiculo_id', $vehiculoId)
            ->with(['usuario', 'pagos'])
            ->orderBy('fecha', 'desc')
            ->get();
    }

    public function findByFecha(string $fecha): Collection
    {
        return $this->model->whereDate('fecha', $fecha)
            ->with(['usuario', 'vehiculo'])
            ->get();
    }

    public function findByEstadoPago(string $estado): Collection
    {
        return $this->model->where('estado_pago', $estado)
            ->with(['usuario', 'vehiculo'])
            ->orderBy('fecha', 'desc')
            ->get();
    }

    public function findPendientes(): Collection
    {
        return $this->findByEstadoPago('Pendiente');
    }
}
