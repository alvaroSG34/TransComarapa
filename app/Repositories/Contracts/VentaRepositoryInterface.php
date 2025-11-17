<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface VentaRepositoryInterface
{
    public function all();
    
    public function find(int $id);
    
    public function findWithRelations(int $id);
    
    public function create(array $data);
    
    public function update(int $id, array $data);
    
    public function delete(int $id);
    
    public function findByUsuario(int $usuarioId): Collection;
    
    public function findByVehiculo(int $vehiculoId): Collection;
    
    public function findByFecha(string $fecha): Collection;
    
    public function findByEstadoPago(string $estado): Collection;
    
    public function findPendientes(): Collection;
}
