<?php

namespace App\Repositories\Contracts;

interface UsuarioRepositoryInterface
{
    public function all();
    
    public function find(int $id);
    
    public function findByCI(string $ci);
    
    public function findByCorreo(string $correo);
    
    public function create(array $data);
    
    public function update(int $id, array $data);
    
    public function delete(int $id);
    
    public function findByRol(string $rol);
    
    public function updateTemaPreferencias(int $id, array $preferencias);
}
