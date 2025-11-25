<?php

namespace App\Http\Middleware;

use App\Models\Visita;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RegistrarVisita
{
    /**
     * Rutas que NO deben ser registradas
     */
    private $rutasExcluidas = [
        '/api/',
        '/_debugbar/',
        '/storage/',
        '/css/',
        '/js/',
        '/images/',
        '/favicon.ico',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('RegistrarVisita: Middleware ejecutado', [
            'method' => $request->method(),
            'path' => $request->path(),
            'url' => $request->url(),
        ]);

        // Registrar la visita ANTES de procesar la respuesta
        // Esto asegura que el contador incluya la visita actual
        if ($request->isMethod('GET')) {
            Log::info('RegistrarVisita: Es una petición GET, intentando registrar visita');
            $this->registrarVisita($request);
        } else {
            Log::info('RegistrarVisita: No es una petición GET, omitiendo registro');
        }

        $response = $next($request);

        return $response;
    }

    /**
     * Registrar la visita en la base de datos
     */
    private function registrarVisita(Request $request): void
    {
        try {
            // $request->path() ya devuelve la ruta sin el dominio, pero sin el "/" inicial
            // Necesitamos normalizarla
            $ruta = $request->path();
            
            Log::info('RegistrarVisita: Ruta original', ['ruta' => $ruta]);
            
            // Normalizar la ruta: siempre empezar con /
            $rutaNormalizada = '/' . $ruta;
            
            // La ruta raíz debe ser "/" (cuando $ruta está vacío)
            if ($ruta === '') {
                $rutaNormalizada = '/';
            }

            Log::info('RegistrarVisita: Ruta normalizada', ['ruta_normalizada' => $rutaNormalizada]);

            // Excluir rutas de assets, API, etc.
            if ($this->debeExcluir($rutaNormalizada)) {
                Log::info('RegistrarVisita: Ruta excluida', ['ruta' => $rutaNormalizada]);
                return;
            }

            Log::info('RegistrarVisita: Intentando crear visita', [
                'ruta' => $rutaNormalizada,
                'ip_address' => $request->ip(),
                'usuario_id' => auth()->id(),
            ]);

            // Registrar la visita
            $visita = Visita::create([
                'ruta' => $rutaNormalizada,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'usuario_id' => auth()->id(),
            ]);

            Log::info('RegistrarVisita: Visita registrada exitosamente', [
                'visita_id' => $visita->id,
                'ruta' => $rutaNormalizada,
            ]);
        } catch (\Exception $e) {
            // Log del error pero no interrumpir la petición
            Log::error('RegistrarVisita: Error al registrar visita', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    /**
     * Verificar si una ruta debe ser excluida
     */
    private function debeExcluir(string $ruta): bool
    {
        foreach ($this->rutasExcluidas as $excluida) {
            if (str_starts_with($ruta, $excluida)) {
                Log::info('RegistrarVisita: Ruta excluida por patrón', [
                    'ruta' => $ruta,
                    'patron' => $excluida,
                ]);
                return true;
            }
        }

        return false;
    }
}
