<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('LandingPage', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rutas para preferencias de tema
    Route::post('/api/user/theme-preferences', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'tema_preferido' => 'sometimes|in:ninos,jovenes,adultos',
            'modo_contraste' => 'sometimes|in:normal,alto',
        ]);

        $request->user()->update($validated);

        return response()->json(['success' => true]);
    });

    // Rutas para Secretaria y Admin
    Route::middleware(['can:isSecretariaOrAdmin'])->group(function () {
        // Rutas - CRUD
        Route::resource('rutas', \App\Http\Controllers\RutaController::class);
        
        // Boletos - CRUD
        Route::resource('boletos', \App\Http\Controllers\BoletoController::class);
        
        // Encomiendas - CRUD
        Route::resource('encomiendas', \App\Http\Controllers\EncomiendaController::class);
        
        // Ventas - Lista y detalle
        Route::resource('ventas', \App\Http\Controllers\VentaController::class);
        
        // Clientes - Solo lectura
        Route::get('/clientes', [\App\Http\Controllers\ClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/{id}', [\App\Http\Controllers\ClienteController::class, 'show'])->name('clientes.show');
    });

    // Rutas solo para Admin
    Route::middleware(['can:isAdmin'])->group(function () {
        // Vehículos - CRUD
        Route::resource('vehiculos', \App\Http\Controllers\VehiculoController::class);
        
        // Estadísticas
        Route::get('/estadisticas', [\App\Http\Controllers\EstadisticaController::class, 'index'])->name('estadisticas.index');
    });

    // Rutas para Cliente
    Route::middleware(['can:isCliente'])->prefix('cliente')->name('cliente.')->group(function () {
        Route::get('/boletos/comprar', function () {
            return Inertia::render('Cliente/ComprarBoleto');
        })->name('boletos.comprar');
        
        Route::get('/encomiendas/enviar', function () {
            return Inertia::render('Cliente/EnviarEncomienda');
        })->name('encomiendas.enviar');
        
        Route::get('/historial', function () {
            return Inertia::render('Cliente/Historial');
        })->name('historial');
    });
});

require __DIR__.'/auth.php';
