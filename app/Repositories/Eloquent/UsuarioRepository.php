<?php

namespace App\Repositories\Eloquent;

use App\Models\Usuario;
use App\Repositories\Contracts\UsuarioRepositoryInterface;

class UsuarioRepository implements UsuarioRepositoryInterface
{
    protected $model;

    public function __construct(Usuario $model)
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

    public function findByCI(string $ci)
    {
        return $this->model->where('ci', $ci)->first();
    }

    public function findByCorreo(string $correo)
    {
        return $this->model->where('correo', $correo)->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $usuario = $this->find($id);
        
        if ($usuario) {
            $usuario->update($data);
            return $usuario->fresh();
        }
        
        return null;
    }

    public function delete(int $id)
    {
        $usuario = $this->find($id);
        
        if ($usuario) {
            return $usuario->delete();
        }
        
        return false;
    }

    public function findByRol(string $rol)
    {
        return $this->model->where('rol', $rol)->get();
    }

    public function updateTemaPreferencias(int $id, array $preferencias)
    {
        $usuario = $this->find($id);
        
        if ($usuario) {
            $usuario->update([
                'tema_preferido' => $preferencias['tema_preferido'] ?? $usuario->tema_preferido,
                'modo_contraste' => $preferencias['modo_contraste'] ?? $usuario->modo_contraste,
                'tamano_fuente' => $preferencias['tamano_fuente'] ?? $usuario->tamano_fuente,
            ]);
            
            return $usuario->fresh();
        }
        
        return null;
    }
}
