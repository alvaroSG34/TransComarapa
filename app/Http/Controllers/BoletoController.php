<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ViajeRepositoryInterface;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use App\Services\VentaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BoletoController extends Controller
{
    protected $viajeRepository;
    protected $usuarioRepository;
    protected $ventaService;

    public function __construct(
        ViajeRepositoryInterface $viajeRepository,
        UsuarioRepositoryInterface $usuarioRepository,
        VentaService $ventaService
    ) {
        $this->viajeRepository = $viajeRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->ventaService = $ventaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DB::table('boletos')
            ->join('viajes', 'boletos.viaje_id', '=', 'viajes.id')
            ->join('rutas', 'viajes.ruta_id', '=', 'rutas.id')
            ->join('ventas', 'boletos.venta_id', '=', 'ventas.id')
            ->join('usuarios', 'ventas.usuario_id', '=', 'usuarios.id')
            ->select(
                'boletos.*',
                'viajes.fecha_salida',
                'viajes.precio',
                'rutas.nombre as ruta_nombre',
                'rutas.origen',
                'rutas.destino',
                'usuarios.nombre as cliente_nombre',
                'usuarios.apellido as cliente_apellido',
                'usuarios.ci as cliente_ci',
                'ventas.estado_pago',
                'ventas.created_at as fecha_venta'
            );

        // Filtros
        if ($request->viaje_id) {
            $query->where('boletos.viaje_id', $request->viaje_id);
        }

        if ($request->estado_pago) {
            $query->where('ventas.estado_pago', $request->estado_pago);
        }

        if ($request->fecha_desde) {
            $query->whereDate('viajes.fecha_salida', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->whereDate('viajes.fecha_salida', '<=', $request->fecha_hasta);
        }

        $boletos = $query->orderBy('viajes.fecha_salida', 'desc')
            ->orderBy('boletos.asiento', 'asc')
            ->get();

        $viajes = $this->viajeRepository->all();

        return Inertia::render('Boletos/Index', [
            'boletos' => $boletos,
            'viajes' => $viajes,
            'filtros' => $request->only(['viaje_id', 'estado_pago', 'fecha_desde', 'fecha_hasta'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener solo viajes disponibles (programados, con asientos, fecha futura)
        $viajesDisponibles = DB::table('viajes')
            ->join('rutas', 'viajes.ruta_id', '=', 'rutas.id')
            ->join('vehiculos', 'viajes.vehiculo_id', '=', 'vehiculos.id')
            ->select(
                'viajes.*',
                'rutas.nombre as ruta_nombre',
                'rutas.origen',
                'rutas.destino',
                'vehiculos.placa as vehiculo_placa'
            )
            ->where('viajes.estado', 'programado')
            ->where('viajes.fecha_salida', '>', now())
            ->orderBy('viajes.fecha_salida', 'asc')
            ->get()
            ->map(function ($viaje) {
                $boletosVendidos = DB::table('boletos')
                    ->where('viaje_id', $viaje->id)
                    ->count();
                
                $viaje->asientos_disponibles = $viaje->asientos_totales - $boletosVendidos;
                $viaje->tiene_asientos = $viaje->asientos_disponibles > 0;
                
                return $viaje;
            })
            ->filter(function ($viaje) {
                return $viaje->tiene_asientos;
            })
            ->values();

        $clientes = $this->usuarioRepository->findByRol('Cliente');

        return Inertia::render('Boletos/Create', [
            'viajes' => $viajesDisponibles,
            'clientes' => $clientes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'viaje_id' => 'required|exists:viajes,id',
            'cliente_id' => 'required|exists:usuarios,id',
            'asiento' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Verificar que el viaje esté disponible
            $viaje = $this->viajeRepository->find($validated['viaje_id']);
            
            if (!$viaje || $viaje->estado !== 'programado') {
                throw new \Exception('El viaje no está disponible para venta.');
            }

            // Verificar asientos disponibles
            $boletosVendidos = DB::table('boletos')
                ->where('viaje_id', $viaje->id)
                ->count();
            
            if ($boletosVendidos >= $viaje->asientos_totales) {
                throw new \Exception('No hay asientos disponibles en este viaje.');
            }

            // Verificar que el asiento no esté ocupado
            $asientoOcupado = DB::table('boletos')
                ->where('viaje_id', $viaje->id)
                ->where('asiento', $validated['asiento'])
                ->exists();

            if ($asientoOcupado) {
                throw new \Exception('El asiento número ' . $validated['asiento'] . ' ya está ocupado.');
            }

            // Crear venta usando el servicio
            $venta = $this->ventaService->crearVenta([
                'usuario_id' => $validated['cliente_id'],
                'monto_total' => $viaje->precio,
                'tipo' => 'Boleto',
                'estado_pago' => 'Pendiente'
            ]);

            // Crear boleto
            DB::table('boletos')->insert([
                'viaje_id' => $viaje->id,
                'asiento' => $validated['asiento'],
                'venta_id' => $venta->id,
                'ruta_id' => $viaje->ruta_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return redirect()->route('boletos.index')
                ->with('success', 'Boleto vendido exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $boleto = DB::table('boletos')
            ->join('viajes', 'boletos.viaje_id', '=', 'viajes.id')
            ->join('rutas', 'viajes.ruta_id', '=', 'rutas.id')
            ->join('vehiculos', 'viajes.vehiculo_id', '=', 'vehiculos.id')
            ->join('ventas', 'boletos.venta_id', '=', 'ventas.id')
            ->join('usuarios', 'ventas.usuario_id', '=', 'usuarios.id')
            ->select(
                'boletos.*',
                'viajes.fecha_salida',
                'viajes.fecha_llegada',
                'viajes.precio',
                'viajes.estado as viaje_estado',
                'rutas.nombre as ruta_nombre',
                'rutas.origen',
                'rutas.destino',
                'vehiculos.placa as vehiculo_placa',
                'vehiculos.marca as vehiculo_marca',
                'vehiculos.modelo as vehiculo_modelo',
                'usuarios.nombre as cliente_nombre',
                'usuarios.apellido as cliente_apellido',
                'usuarios.ci as cliente_ci',
                'usuarios.telefono as cliente_telefono',
                'usuarios.correo as cliente_correo',
                'ventas.estado_pago',
                'ventas.monto_total',
                'ventas.created_at as fecha_venta'
            )
            ->where('boletos.id', $id)
            ->first();

        if (!$boleto) {
            return redirect()->route('boletos.index')
                ->with('error', 'Boleto no encontrado.');
        }

        return Inertia::render('Boletos/Show', [
            'boleto' => $boleto
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Los boletos generalmente no se editan, solo se cancelan
        return redirect()->route('boletos.index')
            ->with('info', 'Los boletos no pueden ser editados. Use la función de cancelación si es necesario.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // No implementado - los boletos no se editan
        return redirect()->route('boletos.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $boleto = DB::table('boletos')->where('id', $id)->first();

            if (!$boleto) {
                throw new \Exception('Boleto no encontrado.');
            }

            // Verificar que el viaje aún no haya iniciado
            $viaje = $this->viajeRepository->find($boleto->viaje_id);
            
            if ($viaje->estado !== 'programado') {
                throw new \Exception('No se puede cancelar el boleto. El viaje ya ha iniciado o finalizado.');
            }

            // Eliminar boleto (esto también elimina la venta por cascade)
            DB::table('boletos')->where('id', $id)->delete();

            DB::commit();

            return redirect()->route('boletos.index')
                ->with('success', 'Boleto cancelado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', $e->getMessage());
        }
    }
}
