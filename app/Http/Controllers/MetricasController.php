<?php

namespace App\Http\Controllers;

use App\Models\Visita;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class MetricasController extends Controller
{
    /**
     * Mostrar estadísticas de visitas
     */
    public function index(Request $request): Response
    {
        // Filtros de fecha
        $fechaInicio = $request->input('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));
        
        // Convertir a Carbon para manipulación
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->endOfDay();

        // 1. Total de visitas
        $totalVisitas = Visita::count();

        // 2. Visitas en el rango de fechas
        $visitasRango = Visita::whereBetween('created_at', [$inicio, $fin])->count();

        // 3. Visitas por día (para el gráfico)
        $visitasPorDia = Visita::selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
            ->whereBetween('created_at', [$inicio, $fin])
            ->groupBy('fecha')
            ->orderBy('fecha', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'fecha' => Carbon::parse($item->fecha)->format('d/m/Y'),
                    'total' => $item->total,
                ];
            });

        // 4. Visitas por ruta (Top 10)
        $visitasPorRuta = Visita::selectRaw('ruta, COUNT(*) as total')
            ->whereBetween('created_at', [$inicio, $fin])
            ->groupBy('ruta')
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();

        // 5. Todas las rutas con visitas (para la tabla con búsqueda)
        $todasLasRutas = Visita::selectRaw('ruta, COUNT(*) as total')
            ->whereBetween('created_at', [$inicio, $fin])
            ->groupBy('ruta')
            ->orderBy('total', 'desc')
            ->get();

        // 6. Detalles de visitas a rutas principales (con información de usuario, IP, fecha, hora)
        // Paginación para evitar tablas muy largas
        $paginaDetalle = $request->input('pagina_detalle', 1);
        $porPagina = 50; // Mostrar 50 visitas por página
        
        $queryDetalle = Visita::with('usuario:id,nombre,correo,rol')
            ->whereIn('ruta', ['/', '/dashboard', '/login', '/register'])
            ->whereBetween('created_at', [$inicio, $fin])
            ->orderBy('created_at', 'desc');
        
        $totalVisitasDetalle = $queryDetalle->count();
        $totalPaginasDetalle = ceil($totalVisitasDetalle / $porPagina);
        
        $rutasPrincipalesDetalle = $queryDetalle
            ->skip(($paginaDetalle - 1) * $porPagina)
            ->take($porPagina)
            ->get()
            ->map(function ($visita) {
                return [
                    'ruta' => $visita->ruta,
                    'usuario_nombre' => $visita->usuario ? $visita->usuario->nombre : 'Invitado',
                    'usuario_email' => $visita->usuario ? $visita->usuario->correo : '-',
                    'ip_address' => $visita->ip_address,
                    'fecha' => Carbon::parse($visita->created_at)->format('d/m/Y'),
                    'hora' => Carbon::parse($visita->created_at)->format('H:i:s'),
                    'timestamp' => $visita->created_at->toDateTimeString(),
                ];
            });

        return Inertia::render('Metricas/Index', [
            'totalVisitas' => $totalVisitas,
            'visitasRango' => $visitasRango,
            'visitasPorDia' => $visitasPorDia,
            'visitasPorRuta' => $visitasPorRuta,
            'todasLasRutas' => $todasLasRutas,
            'rutasPrincipalesDetalle' => $rutasPrincipalesDetalle,
            'paginacionDetalle' => [
                'pagina_actual' => (int)$paginaDetalle,
                'total_paginas' => $totalPaginasDetalle,
                'total_registros' => $totalVisitasDetalle,
                'por_pagina' => $porPagina,
            ],
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }
}
