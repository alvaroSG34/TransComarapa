<?php

namespace App\Providers;

use App\Repositories\Contracts\UsuarioRepositoryInterface;
use App\Repositories\Contracts\VehiculoRepositoryInterface;
use App\Repositories\Contracts\RutaRepositoryInterface;
use App\Repositories\Contracts\VentaRepositoryInterface;
use App\Repositories\Contracts\PagoVentaRepositoryInterface;
use App\Repositories\Contracts\ViajeRepositoryInterface;
use App\Repositories\Eloquent\UsuarioRepository;
use App\Repositories\Eloquent\VehiculoRepository;
use App\Repositories\Eloquent\RutaRepository;
use App\Repositories\Eloquent\VentaRepository;
use App\Repositories\Eloquent\PagoVentaRepository;
use App\Repositories\Eloquent\ViajeRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar implementaciones de repositorios
        $this->app->bind(
            UsuarioRepositoryInterface::class,
            UsuarioRepository::class
        );

        $this->app->bind(
            VehiculoRepositoryInterface::class,
            VehiculoRepository::class
        );

        $this->app->bind(
            RutaRepositoryInterface::class,
            RutaRepository::class
        );

        $this->app->bind(
            VentaRepositoryInterface::class,
            VentaRepository::class
        );

        $this->app->bind(
            PagoVentaRepositoryInterface::class,
            PagoVentaRepository::class
        );

        $this->app->bind(
            ViajeRepositoryInterface::class,
            ViajeRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
