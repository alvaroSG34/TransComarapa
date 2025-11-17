<?php

namespace App\Repositories\Contracts;

interface ViajeRepositoryInterface
{
    public function all();
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function disponibles();
    public function porRuta($rutaId);
    public function porFecha($fecha);
    public function cambiarEstado($id, $estado);
}
