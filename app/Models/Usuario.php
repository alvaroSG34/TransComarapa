<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'ci',
        'telefono',
        'correo',
        'password',
        'rol',
        'img_url',
        'tema_preferido',
        'modo_contraste',
        'tamano_fuente',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Accessor para compatibilidad con Breeze (name -> nombre)
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => trim(($this->nombre ?? '') . ' ' . ($this->apellido ?? '')),
        );
    }

    // Accessor para compatibilidad con Breeze (email -> correo)
    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->correo,
        );
    }
    
    // Mutator para asignar nombre cuando se usa el campo "name" de Breeze
    public function setNameAttribute($value)
    {
        // Dividir el nombre en dos partes (nombre y apellido)
        $parts = explode(' ', $value, 2);
        $this->attributes['nombre'] = $parts[0] ?? '';
        $this->attributes['apellido'] = $parts[1] ?? '';
    }
    
    // Mutator para asignar correo cuando se usa el campo "email" de Breeze
    public function setEmailAttribute($value)
    {
        $this->attributes['correo'] = $value;
    }

    // Relaciones
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'conductor_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'usuario_id');
    }
}
