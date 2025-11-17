<?php

namespace App\Repositories\Contracts;

interface VehiculoRepositoryInterface
{
    public function all();
    
    public function find(int $id);
    
    public function findByPlaca(string $placa);
    
    public function create(array $data);
    
    public function update(int $id, array $data);
    
    public function delete(int $id);
    
    public function findByConductor(int $conductorId);
    
    public function findDisponibles();
}
