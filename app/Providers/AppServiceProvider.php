<?php

namespace App\Providers;

use App\Events\PagoVentaCreated;
use App\Events\PagoVentaUpdated;
use App\Listeners\ActualizarEstadoVenta;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Registrar eventos y listeners
        Event::listen(PagoVentaCreated::class, ActualizarEstadoVenta::class);
        Event::listen(PagoVentaUpdated::class, ActualizarEstadoVenta::class);
    }
}
