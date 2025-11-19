<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class EstadisticaController extends Controller
{
    public function index(Request $request)
    {
        $fechaInicio = $request->fecha_inicio ? Carbon::parse($request->fecha_inicio) : Carbon::now()->startOfMonth();
        $fechaFin = $request->fecha_fin ? Carbon::parse($request->fecha_fin)->endOfDay() : Carbon::now()->endOfDay();

        // 1. Ingresos totales por tipo (solo ventas Pagadas)
        $ingresos = DB::table('ventas')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('estado_pago', 'Pagado')
            ->select('tipo', DB::raw('SUM(monto_total) as total'), DB::raw('COUNT(*) as cantidad'))
            ->groupBy('tipo')
            ->get();

        $totalBoletos = $ingresos->where('tipo', 'Boleto')->first()->total ?? 0;
        $totalEncomiendas = $ingresos->where('tipo', 'Encomienda')->first()->total ?? 0;
        $cantidadBoletos = $ingresos->where('tipo', 'Boleto')->first()->cantidad ?? 0;
        $cantidadEncomiendas = $ingresos->where('tipo', 'Encomienda')->first()->cantidad ?? 0;

        // 2. Rutas más usadas (Top 5) - solo ventas Pagadas
        $rutasMasUsadas = DB::table('ventas')
            ->whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
            ->where('ventas.estado_pago', 'Pagado')
            ->leftJoin('boletos', 'ventas.id', '=', 'boletos.venta_id')
            ->leftJoin('encomiendas', 'ventas.id', '=', 'encomiendas.venta_id')
            ->leftJoin('viajes', 'boletos.viaje_id', '=', 'viajes.id')
            ->leftJoin('rutas as ruta_boleto', 'viajes.ruta_id', '=', 'ruta_boleto.id')
            ->leftJoin('rutas as ruta_encomienda', 'encomiendas.ruta_id', '=', 'ruta_encomienda.id')
            ->select(
                DB::raw('COALESCE(ruta_boleto.nombre, ruta_encomienda.nombre) as nombre_ruta'),
                DB::raw('COUNT(*) as total_ventas'),
                DB::raw('SUM(ventas.monto_total) as total_ingresos')
            )
            ->whereNotNull(DB::raw('COALESCE(ruta_boleto.nombre, ruta_encomienda.nombre)'))
            ->groupBy('nombre_ruta')
            ->orderByDesc('total_ventas')
            ->limit(5)
            ->get();

        // 3. Vehículos con mayor cantidad de viajes realizados (Top 5)
        $vehiculosTop = DB::table('viajes')
            ->whereBetween('viajes.fecha_salida', [$fechaInicio, $fechaFin])
            ->whereIn('viajes.estado', ['en_curso', 'finalizado'])
            ->join('vehiculos', 'viajes.vehiculo_id', '=', 'vehiculos.id')
            ->select(
                'vehiculos.placa',
                'vehiculos.marca',
                'vehiculos.modelo',
                DB::raw('COUNT(viajes.id) as total_viajes')
            )
            ->groupBy('vehiculos.id', 'vehiculos.placa', 'vehiculos.marca', 'vehiculos.modelo')
            ->orderByDesc('total_viajes')
            ->limit(5)
            ->get();

        // 4. Datos para el gráfico (Ingresos por día) - solo ventas Pagadas
        $graficoDiario = DB::table('ventas')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('estado_pago', 'Pagado')
            ->select(
                DB::raw('DATE(fecha) as dia'),
                'tipo',
                DB::raw('SUM(monto_total) as total')
            )
            ->groupBy('dia', 'tipo')
            ->orderBy('dia')
            ->get();

        // Procesar datos para gráfico fácil de consumir en frontend
        $dias = [];
        $periodo = Carbon::parse($fechaInicio);
        while ($periodo <= $fechaFin) {
            $fechaStr = $periodo->format('Y-m-d');
            $datoBoleto = $graficoDiario->where('dia', $fechaStr)->where('tipo', 'Boleto')->first();
            $datoEncomienda = $graficoDiario->where('dia', $fechaStr)->where('tipo', 'Encomienda')->first();

            $dias[] = [
                'fecha' => $fechaStr,
                'boleto' => $datoBoleto ? (float)$datoBoleto->total : 0,
                'encomienda' => $datoEncomienda ? (float)$datoEncomienda->total : 0,
            ];
            $periodo->addDay();
        }

        return Inertia::render('Estadisticas/Index', [
            'kpis' => [
                'total_general' => $totalBoletos + $totalEncomiendas,
                'total_boletos' => (float)$totalBoletos,
                'total_encomiendas' => (float)$totalEncomiendas,
                'cantidad_boletos' => (int)$cantidadBoletos,
                'cantidad_encomiendas' => (int)$cantidadEncomiendas,
            ],
            'rutas_top' => $rutasMasUsadas,
            'vehiculos_top' => $vehiculosTop,
            'grafico_datos' => $dias,
            'filtros' => [
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'fecha_fin' => $fechaFin->format('Y-m-d'),
            ]
        ]);
    }
}
