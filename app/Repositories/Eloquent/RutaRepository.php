<?php

namespace App\Repositories\Eloquent;

use App\Models\Ruta;
use App\Repositories\Contracts\RutaRepositoryInterface;

class RutaRepository implements RutaRepositoryInterface
{
    protected $model;

    public function __construct(Ruta $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->all();
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
        $ruta = $this->find($id);
        
        if ($ruta) {
            $ruta->update($data);
            return $ruta->fresh();
        }
        
        return null;
    }

    public function delete(int $id)
    {
        $ruta = $this->find($id);
        
        if ($ruta) {
            return $ruta->delete();
        }
        
        return false;
    }

    public function findByOrigen(string $origen)
    {
        return $this->model->where('origen', 'like', "%{$origen}%")->get();
    }

    public function findByDestino(string $destino)
    {
        return $this->model->where('destino', 'like', "%{$destino}%")->get();
    }

    public function findByOrigenDestino(string $origen, string $destino)
    {
        return $this->model->where('origen', 'like', "%{$origen}%")
            ->where('destino', 'like', "%{$destino}%")
            ->first();
    }
}
