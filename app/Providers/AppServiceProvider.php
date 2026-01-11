<?php

namespace App\Providers;

use App\Events\PagoVentaCreated;
use App\Events\PagoVentaUpdated;
use App\Listeners\ActualizarEstadoVenta;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar UserProvider personalizado para manejar campo 'correo' como 'email'
        $this->app['auth']->provider('correo', function ($app, array $config) {
            return new \App\Auth\CorreoUserProvider($app['hash'], $config['model']);
        });
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

        // Definir Gates para autorización por roles
        Gate::define('isAdmin', function ($user) {
            return $user->rol === 'Admin';
        });

        Gate::define('isSecretaria', function ($user) {
            return $user->rol === 'Secretaria';
        });

        Gate::define('isCliente', function ($user) {
            return $user->rol === 'Cliente';
        });

        Gate::define('isSecretariaOrAdmin', function ($user) {
            return in_array($user->rol, ['Secretaria', 'Admin']);
        });
    }
}
