<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface PagoVentaRepositoryInterface
{
    public function all();
    
    public function find(int $id);
    
    public function create(array $data);
    
    public function update(int $id, array $data);
    
    public function delete(int $id);
    
    public function findByVenta(int $ventaId): Collection;
    
    public function findPendientesByVenta(int $ventaId): Collection;
    
    public function findPagadosByVenta(int $ventaId): Collection;
    
    public function getTotalPagadoByVenta(int $ventaId): float;
}
