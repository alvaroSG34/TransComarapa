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
    Route::match(['patch', 'post'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
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
        
        // Viajes - CRUD
        Route::resource('viajes', \App\Http\Controllers\ViajeController::class);
        Route::post('/viajes/{id}/cambiar-estado', [\App\Http\Controllers\ViajeController::class, 'cambiarEstado'])->name('viajes.cambiar-estado');
        
        // Boletos - CRUD
        Route::resource('boletos', \App\Http\Controllers\BoletoController::class);
        Route::post('/boletos/{id}/marcar-pagado', [\App\Http\Controllers\BoletoController::class, 'marcarPagado'])->name('boletos.marcar-pagado');
        Route::get('/boletos-buscar-cliente', [\App\Http\Controllers\BoletoController::class, 'buscarCliente'])->name('boletos.buscar-cliente');
        Route::post('/boletos-registrar-cliente', [\App\Http\Controllers\BoletoController::class, 'registrarCliente'])->name('boletos.registrar-cliente');
        Route::get('/boletos/viaje/{id}/asientos-ocupados', [\App\Http\Controllers\BoletoController::class, 'obtenerAsientosOcupados'])->name('boletos.asientos-ocupados');
        Route::post('/boletos/{id}/verificar-estado', [\App\Http\Controllers\BoletoController::class, 'verificarEstadoPago'])->name('boletos.verificar-estado');
        Route::post('/boletos/{id}/reintentar-qr', [\App\Http\Controllers\BoletoController::class, 'reintentarQr'])->name('boletos.reintentar-qr');
        
        // Encomiendas - CRUD
        Route::resource('encomiendas', \App\Http\Controllers\EncomiendaController::class);
        Route::get('/encomiendas-buscar-cliente', [\App\Http\Controllers\EncomiendaController::class, 'buscarCliente'])->name('encomiendas.buscar-cliente');
        Route::post('/encomiendas-registrar-cliente', [\App\Http\Controllers\EncomiendaController::class, 'registrarCliente'])->name('encomiendas.registrar-cliente');
        Route::post('/encomiendas/{id}/pago-destino', [\App\Http\Controllers\EncomiendaController::class, 'registrarPagoDestino'])->name('encomiendas.pago-destino');
        Route::post('/encomiendas/{id}/verificar-estado', [\App\Http\Controllers\EncomiendaController::class, 'verificarEstadoPago'])->name('encomiendas.verificar-estado');
        
        // Ventas - Lista y detalle
        Route::resource('ventas', \App\Http\Controllers\VentaController::class)->only(['index', 'show']);
        Route::post('/ventas/{id}/marcar-pagado', [\App\Http\Controllers\VentaController::class, 'marcarPagado'])->name('ventas.marcar-pagado');
        Route::post('/ventas/{id}/cancelar', [\App\Http\Controllers\VentaController::class, 'cancelar'])->name('ventas.cancelar');
    });

    // Rutas solo para Admin
    Route::middleware(['can:isAdmin'])->group(function () {
        // Clientes - Gestión completa (Ver, Banear)
        Route::get('/clientes', [\App\Http\Controllers\ClienteController::class, 'index'])->name('clientes.index');
        Route::get('/clientes/{id}', [\App\Http\Controllers\ClienteController::class, 'show'])->name('clientes.show');
        Route::delete('/clientes/{id}', [\App\Http\Controllers\ClienteController::class, 'destroy'])->name('clientes.destroy');
        Route::post('/clientes/{id}/restore', [\App\Http\Controllers\ClienteController::class, 'restore'])->name('clientes.restore');

        // Vehículos - CRUD
        Route::resource('vehiculos', \App\Http\Controllers\VehiculoController::class);
        
        // Conductores - CRUD
        Route::resource('conductores', \App\Http\Controllers\ConductorController::class);
        
        // Secretarias - CRUD
        Route::resource('secretarias', \App\Http\Controllers\SecretariaController::class);
        
        // Estadísticas
        Route::get('/estadisticas', [\App\Http\Controllers\EstadisticaController::class, 'index'])->name('estadisticas.index');
    });

    // Rutas para Cliente
    Route::middleware(['can:isCliente'])->prefix('cliente')->name('cliente.')->group(function () {
        // Boletos
        Route::get('/boletos/comprar', [\App\Http\Controllers\ClienteBoletoController::class, 'mostrarRutas'])->name('boletos.comprar');
        Route::get('/boletos/ruta/{rutaId}/viajes', [\App\Http\Controllers\ClienteBoletoController::class, 'mostrarViajes'])->name('boletos.viajes');
        Route::get('/boletos/viaje/{viajeId}/ruta/{rutaId}/form', [\App\Http\Controllers\ClienteBoletoController::class, 'mostrarFormularioCompra'])->name('boletos.form');
        Route::get('/boletos/viaje/{viajeId}/asientos-ocupados', [\App\Http\Controllers\ClienteBoletoController::class, 'obtenerAsientosOcupados'])->name('boletos.asientos-ocupados');
        Route::post('/boletos/procesar-compra', [\App\Http\Controllers\ClienteBoletoController::class, 'procesarCompra'])->name('boletos.procesar-compra');
        
        Route::get('/encomiendas/enviar', function () {
            return Inertia::render('Cliente/EnviarEncomienda');
        })->name('encomiendas.enviar');
        
        Route::get('/historial', [\App\Http\Controllers\ClienteHistorialController::class, 'index'])->name('historial');
        
        // Verificar estado de pago de mis boletos
        Route::post('/boletos/{id}/verificar-estado', [\App\Http\Controllers\ClienteHistorialController::class, 'verificarEstadoPago'])->name('boletos.verificar-estado');
    });
});

// Rutas para contador de visitas (públicas)
Route::get('/api/visitas/contador', [\App\Http\Controllers\VisitaController::class, 'obtenerContadorPorRuta'])->name('visitas.contador');
Route::get('/api/visitas/total', [\App\Http\Controllers\VisitaController::class, 'obtenerContadorTotal'])->name('visitas.total');
Route::post('/api/visitas/multiples', [\App\Http\Controllers\VisitaController::class, 'obtenerContadoresMultiples'])->name('visitas.multiples');

require __DIR__.'/auth.php';
