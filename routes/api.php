<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClienteApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Búsqueda de clientes
    Route::get('/clientes/buscar', [ClienteApiController::class, 'buscar']);
    
    // Registro rápido de clientes
    Route::post('/clientes/registro-rapido', [ClienteApiController::class, 'registroRapido']);
});
