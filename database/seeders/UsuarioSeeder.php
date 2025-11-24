<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Usuario::create([
            'nombre' => 'Admin',
            'apellido' => 'Sistema',
            'ci' => '0000000',
            'telefono' => null,
            'correo' => 'admin@gmail.com',
            'password' => 'admin123', // Se hasheará automáticamente por el cast 'hashed'
            'rol' => 'Admin',
            'img_url' => null,
        ]);
    }
}

