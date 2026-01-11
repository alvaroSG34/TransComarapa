<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'pais',
        'moneda',
        'ci',
        'telefono',
        'codigo_pais_telefono',
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
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'name',
        'email',
        'img_url_full',
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

    /**
     * Get the email address for password reset.
     * Laravel busca este método para saber qué campo usar para reset
     */
    public function getEmailForPasswordReset()
    {
        return $this->correo;
    }

    // Accessor para obtener la URL completa de la imagen
    protected function imgUrlFull(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->img_url) {
                    return null;
                }
                
                // Si ya tiene la URL completa, retornarla tal cual
                if (str_starts_with($this->img_url, 'http://') || str_starts_with($this->img_url, 'https://')) {
                    return $this->img_url;
                }
                
                // Si tiene /storage/ al inicio, quitarlo (por compatibilidad con registros antiguos)
                $path = $this->img_url;
                if (str_starts_with($path, '/storage/')) {
                    $path = substr($path, 9); // Remover '/storage/'
                }
                
                // Construir la URL completa usando asset()
                return asset('storage/' . $path);
            }
        );
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
