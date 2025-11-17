<?php

namespace App\Repositories\Contracts;

interface RutaRepositoryInterface
{
    public function all();
    
    public function find(int $id);
    
    public function create(array $data);
    
    public function update(int $id, array $data);
    
    public function delete(int $id);
    
    public function findByOrigen(string $origen);
    
    public function findByDestino(string $destino);
    
    public function findByOrigenDestino(string $origen, string $destino);
}
