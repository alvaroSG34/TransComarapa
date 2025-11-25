<?php

namespace App\Http\Controllers;

use App\Models\Visita;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VisitaController extends Controller
{
    /**
     * Obtener el contador de visitas para una ruta específica
     */
    public function obtenerContadorPorRuta(Request $request): JsonResponse
    {
        $ruta = $request->input('ruta', '/');
        
        // Normalizar la ruta
        if (!str_starts_with($ruta, '/')) {
            $ruta = '/' . $ruta;
        }
        
        // La ruta raíz debe ser "/"
        if ($ruta === '') {
            $ruta = '/';
        }

        $contador = Visita::contarPorRuta($ruta);

        return response()->json([
            'ruta' => $ruta,
            'contador' => $contador,
        ]);
    }

    /**
     * Obtener el contador total de visitas
     */
    public function obtenerContadorTotal(): JsonResponse
    {
        $contador = Visita::contarTotal();

        return response()->json([
            'contador' => $contador,
        ]);
    }

    /**
     * Obtener contadores para múltiples rutas
     */
    public function obtenerContadoresMultiples(Request $request): JsonResponse
    {
        $rutas = $request->input('rutas', []);
        $contadores = [];

        foreach ($rutas as $ruta) {
            // Normalizar la ruta
            if (!str_starts_with($ruta, '/')) {
                $ruta = '/' . $ruta;
            }
            $contadores[$ruta] = Visita::contarPorRuta($ruta);
        }

        return response()->json([
            'contadores' => $contadores,
        ]);
    }
}
