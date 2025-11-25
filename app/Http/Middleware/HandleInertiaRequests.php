<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Detectar si es día o noche según horario de Bolivia (6 AM - 6 PM)
        $currentHour = now()->setTimezone('America/La_Paz')->hour;
        $timeMode = ($currentHour >= 6 && $currentHour < 18) ? 'day' : 'night';

        // Obtener contador de visitas para la ruta actual
        // Esto se ejecuta DESPUÉS de RegistrarVisita, por lo que incluirá la visita actual
        $contadorVisitas = null;
        if ($request->isMethod('GET')) {
            try {
                $ruta = $request->path();
                $rutaNormalizada = '/' . ($ruta === '' ? '' : $ruta);
                if ($ruta === '') {
                    $rutaNormalizada = '/';
                }
                
                Log::info('HandleInertiaRequests: Obteniendo contador de visitas', [
                    'ruta_original' => $ruta,
                    'ruta_normalizada' => $rutaNormalizada,
                ]);
                
                // Solo obtener contador si no es una ruta excluida
                $rutasExcluidas = ['/api/', '/_debugbar/', '/storage/', '/css/', '/js/', '/images/', '/favicon.ico'];
                $debeExcluir = false;
                foreach ($rutasExcluidas as $excluida) {
                    if (str_starts_with($rutaNormalizada, $excluida)) {
                        $debeExcluir = true;
                        Log::info('HandleInertiaRequests: Ruta excluida', ['ruta' => $rutaNormalizada]);
                        break;
                    }
                }
                
                if (!$debeExcluir) {
                    $contadorVisitas = \App\Models\Visita::where('ruta', $rutaNormalizada)->count();
                    Log::info('HandleInertiaRequests: Contador obtenido', [
                        'ruta' => $rutaNormalizada,
                        'contador' => $contadorVisitas,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('HandleInertiaRequests: Error al obtener contador', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } else {
            Log::info('HandleInertiaRequests: No es GET, omitiendo contador');
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'timeMode' => $timeMode,
            'currentHour' => $currentHour,
            'contadorVisitas' => $contadorVisitas,
        ];
    }
}
