<?php

namespace App\Repositories\Eloquent;

use App\Models\Viaje;
use App\Repositories\Contracts\ViajeRepositoryInterface;

class ViajeRepository implements ViajeRepositoryInterface
{
    protected $model;

    public function __construct(Viaje $viaje)
    {
        $this->model = $viaje;
    }

    public function all()
    {
        return $this->model->with(['ruta', 'vehiculo'])
            ->withCount('boletos')
            ->orderBy('fecha_salida', 'desc')
            ->get();
    }

    public function find($id)
    {
        return $this->model->with(['ruta', 'vehiculo', 'boletos'])->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $viaje = $this->model->find($id);
        if ($viaje) {
            $viaje->update($data);
            return $viaje;
        }
        return null;
    }

    public function delete($id)
    {
        $viaje = $this->model->find($id);
        if ($viaje) {
            return $viaje->delete();
        }
        return false;
    }

    public function disponibles()
    {
        return $this->model->disponibles()
            ->with(['ruta', 'vehiculo'])
            ->orderBy('fecha_salida', 'asc')
            ->get();
    }

    public function porRuta($rutaId)
    {
        return $this->model->porRuta($rutaId)
            ->with(['ruta', 'vehiculo'])
            ->orderBy('fecha_salida', 'desc')
            ->get();
    }

    public function porFecha($fecha)
    {
        return $this->model->porFecha($fecha)
            ->with(['ruta', 'vehiculo'])
            ->orderBy('fecha_salida', 'asc')
            ->get();
    }

    public function cambiarEstado($id, $estado)
    {
        $viaje = $this->model->find($id);
        if ($viaje) {
            $viaje->estado = $estado;
            $viaje->save();
            return $viaje;
        }
        return null;
    }
}
