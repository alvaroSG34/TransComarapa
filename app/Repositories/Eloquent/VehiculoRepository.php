<?php

namespace App\Repositories\Eloquent;

use App\Models\Vehiculo;
use App\Repositories\Contracts\VehiculoRepositoryInterface;

class VehiculoRepository implements VehiculoRepositoryInterface
{
    protected $model;

    public function __construct(Vehiculo $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->with('conductor')->get();
    }

    public function find(int $id)
    {
        return $this->model->with('conductor')->find($id);
    }

    public function findByPlaca(string $placa)
    {
        return $this->model->where('placa', $placa)
            ->with('conductor')
            ->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $vehiculo = $this->model->find($id);
        
        if ($vehiculo) {
            $vehiculo->update($data);
            return $vehiculo->fresh(['conductor']);
        }
        
        return null;
    }

    public function delete(int $id)
    {
        $vehiculo = $this->model->find($id);
        
        if ($vehiculo) {
            return $vehiculo->delete();
        }
        
        return false;
    }

    public function findByConductor(int $conductorId)
    {
        return $this->model->where('conductor_id', $conductorId)
            ->with('conductor')
            ->get();
    }

    public function findDisponibles()
    {
        return $this->model->where('estado', 'disponible')
            ->with('conductor')
            ->get();
    }
}
