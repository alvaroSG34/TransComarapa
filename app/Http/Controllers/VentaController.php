<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\VentaRepositoryInterface;
use App\Services\VentaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VentaController extends Controller
{
    protected $ventaRepository;
    protected $ventaService;

    public function __construct(
        VentaRepositoryInterface $ventaRepository,
        VentaService $ventaService
    ) {
        $this->ventaRepository = $ventaRepository;
        $this->ventaService = $ventaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DB::table('ventas')
            ->join('usuarios', 'ventas.usuario_id', '=', 'usuarios.id')
            ->leftJoin('boletos', 'ventas.id', '=', 'boletos.venta_id')
            ->leftJoin('encomiendas', 'ventas.id', '=', 'encomiendas.venta_id')
            ->leftJoin('viajes', 'boletos.viaje_id', '=', 'viajes.id')
            ->leftJoin('rutas as ruta_boleto', 'viajes.ruta_id', '=', 'ruta_boleto.id')
            ->leftJoin('rutas as ruta_encomienda', 'encomiendas.ruta_id', '=', 'ruta_encomienda.id')
            ->select(
                'ventas.id',
                'ventas.fecha',
                'ventas.monto_total',
                'ventas.tipo',
                'ventas.estado_pago',
                'usuarios.nombre as cliente_nombre',
                'usuarios.apellido as cliente_apellido',
                'usuarios.ci as cliente_ci',
                // Datos del boleto
                'boletos.asiento',
                'viajes.fecha_salida',
                'ruta_boleto.nombre as ruta_boleto_nombre',
                'ruta_boleto.origen as ruta_boleto_origen',
                'ruta_boleto.destino as ruta_boleto_destino',
                // Datos de la encomienda
                'encomiendas.peso',
                'encomiendas.nombre_destinatario',
                'encomiendas.modalidad_pago',
                'ruta_encomienda.nombre as ruta_encomienda_nombre',
                'ruta_encomienda.origen as ruta_encomienda_origen',
                'ruta_encomienda.destino as ruta_encomienda_destino'
            );

        // Filtros
        if ($request->tipo && $request->tipo !== 'todos') {
            $query->where('ventas.tipo', $request->tipo);
        }

        if ($request->estado_pago && $request->estado_pago !== 'todos') {
            $query->where('ventas.estado_pago', $request->estado_pago);
        }

        if ($request->fecha_desde) {
            $query->whereDate('ventas.fecha', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->whereDate('ventas.fecha', '<=', $request->fecha_hasta);
        }

        if ($request->cliente_busqueda) {
            $busqueda = $request->cliente_busqueda;
            $query->where(function($q) use ($busqueda) {
                $q->where('usuarios.ci', 'LIKE', "%{$busqueda}%")
                  ->orWhere('usuarios.nombre', 'LIKE', "%{$busqueda}%")
                  ->orWhere('usuarios.apellido', 'LIKE', "%{$busqueda}%");
            });
        }

        $ventas = $query->orderBy('ventas.fecha', 'desc')
            ->orderBy('ventas.id', 'desc')
            ->get();

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
            'filtros' => $request->only(['tipo', 'estado_pago', 'fecha_desde', 'fecha_hasta', 'cliente_busqueda'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Las ventas se crean desde Boletos o Encomiendas
        return redirect()->route('dashboard');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Las ventas se crean desde Boletos o Encomiendas
        return redirect()->route('dashboard');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $venta = DB::table('ventas')
            ->join('usuarios', 'ventas.usuario_id', '=', 'usuarios.id')
            ->leftJoin('boletos', 'ventas.id', '=', 'boletos.venta_id')
            ->leftJoin('encomiendas', 'ventas.id', '=', 'encomiendas.venta_id')
            ->leftJoin('viajes', 'boletos.viaje_id', '=', 'viajes.id')
            ->leftJoin('vehiculos', 'viajes.vehiculo_id', '=', 'vehiculos.id')
            ->leftJoin('usuarios as conductor', 'vehiculos.conductor_id', '=', 'conductor.id')
            ->leftJoin('rutas as ruta_boleto', 'viajes.ruta_id', '=', 'ruta_boleto.id')
            ->leftJoin('rutas as ruta_encomienda', 'encomiendas.ruta_id', '=', 'ruta_encomienda.id')
            ->where('ventas.id', $id)
            ->select(
                'ventas.*',
                // Cliente
                'usuarios.nombre as cliente_nombre',
                'usuarios.apellido as cliente_apellido',
                'usuarios.ci as cliente_ci',
                'usuarios.telefono as cliente_telefono',
                'usuarios.correo as cliente_correo',
                // Boleto
                'boletos.asiento',
                'viajes.id as viaje_id',
                'viajes.fecha_salida',
                'viajes.estado as viaje_estado',
                'viajes.precio as viaje_precio',
                'vehiculos.placa as vehiculo_placa',
                'vehiculos.marca as vehiculo_marca',
                'vehiculos.modelo as vehiculo_modelo',
                'conductor.nombre as conductor_nombre',
                'conductor.apellido as conductor_apellido',
                'ruta_boleto.id as ruta_boleto_id',
                'ruta_boleto.nombre as ruta_boleto_nombre',
                'ruta_boleto.origen as ruta_boleto_origen',
                'ruta_boleto.destino as ruta_boleto_destino',
                // Encomienda
                'encomiendas.peso',
                'encomiendas.descripcion',
                'encomiendas.nombre_destinatario',
                'encomiendas.img_url',
                'encomiendas.modalidad_pago',
                'ruta_encomienda.id as ruta_encomienda_id',
                'ruta_encomienda.nombre as ruta_encomienda_nombre',
                'ruta_encomienda.origen as ruta_encomienda_origen',
                'ruta_encomienda.destino as ruta_encomienda_destino'
            )
            ->first();

        if (!$venta) {
            abort(404);
        }

        return Inertia::render('Ventas/Show', [
            'venta' => $venta
        ]);
    }

    /**
     * Marcar venta como pagada
     */
    public function marcarPagado(int $id)
    {
        try {
            $venta = DB::table('ventas')->where('id', $id)->first();

            if (!$venta) {
                return back()->with('error', 'Venta no encontrada.');
            }

            if ($venta->estado_pago === 'Pagado') {
                return back()->with('info', 'La venta ya está marcada como pagada.');
            }

            DB::table('ventas')
                ->where('id', $id)
                ->update([
                    'estado_pago' => 'Pagado',
                    'updated_at' => now()
                ]);

            return back()->with('success', 'Venta marcada como pagada exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar el estado: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar venta
     */
    public function cancelar(int $id)
    {
        DB::beginTransaction();

        try {
            $venta = DB::table('ventas')->where('id', $id)->first();

            if (!$venta) {
                return back()->with('error', 'Venta no encontrada.');
            }

            if ($venta->estado_pago === 'Cancelado') {
                return back()->with('info', 'La venta ya está cancelada.');
            }

            // Si es un boleto, verificar que el viaje no haya iniciado
            if ($venta->tipo === 'Boleto') {
                $boleto = DB::table('boletos')->where('venta_id', $id)->first();
                if ($boleto) {
                    $viaje = DB::table('viajes')->where('id', $boleto->viaje_id)->first();
                    if ($viaje && $viaje->estado !== 'programado') {
                        return back()->with('error', 'No se puede cancelar un boleto de un viaje que ya inició o finalizó.');
                    }
                }
            }

            // Cancelar venta
            DB::table('ventas')
                ->where('id', $id)
                ->update([
                    'estado_pago' => 'Cancelado',
                    'updated_at' => now()
                ]);

            DB::commit();

            return redirect()->route('ventas.index')
                ->with('success', 'Venta cancelada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cancelar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        // Las ventas no se editan directamente, solo se cambia estado
        return redirect()->route('ventas.show', $id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        // Las ventas no se editan directamente, solo se cambia estado
        return redirect()->route('ventas.show', $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        // Usar el método cancelar en su lugar
        return $this->cancelar($id);
    }
}
