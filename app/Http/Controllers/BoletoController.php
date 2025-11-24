<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ViajeRepositoryInterface;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use App\Services\VentaService;
use App\Services\PagoFacilService;
use App\Services\PagoService;
use App\Models\PagoVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BoletoController extends Controller
{
    protected $viajeRepository;
    protected $usuarioRepository;
    protected $ventaService;
    protected $pagoFacilService;
    protected $pagoService;

    public function __construct(
        ViajeRepositoryInterface $viajeRepository,
        UsuarioRepositoryInterface $usuarioRepository,
        VentaService $ventaService,
        PagoFacilService $pagoFacilService,
        PagoService $pagoService
    ) {
        $this->viajeRepository = $viajeRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->ventaService = $ventaService;
        $this->pagoFacilService = $pagoFacilService;
        $this->pagoService = $pagoService;
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
            ->leftJoin('pago_ventas', function($join) {
                $join->on('ventas.id', '=', 'pago_ventas.venta_id')
                     ->where('pago_ventas.num_cuota', '=', 1); // Solo la primera cuota
            })
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
                'ventas.monto_total',
                'ventas.created_at as fecha_venta',
                DB::raw('COALESCE(pago_ventas.metodo_pago, NULL) as metodo_pago'),
                DB::raw('COALESCE(pago_ventas.qr_base64, NULL) as qr_base64'),
                DB::raw('COALESCE(pago_ventas.transaction_id, NULL) as transaction_id'),
                DB::raw('COALESCE(pago_ventas.payment_method_transaction_id, NULL) as payment_method_transaction_id'),
                DB::raw('COALESCE(pago_ventas.estado_pago, NULL) as pago_estado')
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
            'metodo_pago' => 'required|in:Efectivo,QR',
        ]);

        return DB::transaction(function () use ($validated) {
            try {
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
                $ventaData = [
                    'fecha' => now(),
                    'monto_total' => $viaje->precio,
                    'usuario_id' => $validated['cliente_id'],
                    'vehiculo_id' => $viaje->vehiculo_id,
                    'boletos' => [
                        [
                            'asiento' => $validated['asiento'],
                            'ruta_id' => $viaje->ruta_id,
                            'viaje_id' => $viaje->id
                        ]
                    ]
                ];

                $venta = $this->ventaService->crearVentaBoleto($ventaData);

                // Si el método de pago es QR, generar QR
                if ($validated['metodo_pago'] === 'QR') {
                    // Crear PagoVenta
                    $pagoVenta = $this->pagoService->crearPago([
                        'venta_id' => $venta->id,
                        'num_cuota' => 1,
                        'monto' => $venta->monto_total,
                        'metodo_pago' => 'QR',
                        'estado_pago' => 'Pendiente',
                    ]);

                    // Generar QR
                    $resultadoQr = $this->pagoFacilService->generarQr($pagoVenta);

                    if (!$resultadoQr['success']) {
                        throw new \Exception('Error al generar QR: ' . ($resultadoQr['error'] ?? 'Error desconocido'));
                    }

                    // Obtener datos del viaje y cliente para re-renderizar la vista
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

                    // Retornar a Create con datos del QR
                    return Inertia::render('Boletos/Create', [
                        'viajes' => $viajesDisponibles,
                        'clientes' => $clientes,
                        'qr_data' => [
                            'qr_base64' => $resultadoQr['pago_venta']->qr_base64,
                            'transaction_id' => $resultadoQr['pago_venta']->transaction_id,
                            'boleto_id' => $venta->boletos->first()->id,
                        ],
                        'success' => 'Boleto creado exitosamente. QR generado.'
                    ]);
                }

                // Si es Efectivo, redirigir normalmente
                return redirect()->route('boletos.index')
                    ->with('success', 'Boleto vendido exitosamente.');

            } catch (\Exception $e) {
                throw $e;
            }
        });
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
            ->leftJoin('pago_ventas', function($join) {
                $join->on('ventas.id', '=', 'pago_ventas.venta_id')
                     ->where('pago_ventas.num_cuota', '=', 1);
            })
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
                'ventas.created_at as fecha_venta',
                'pago_ventas.metodo_pago',
                'pago_ventas.qr_base64',
                'pago_ventas.transaction_id',
                'pago_ventas.payment_method_transaction_id',
                'pago_ventas.estado_pago as pago_estado'
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
    
    /**
     * Marcar boleto como pagado
     */
    public function marcarPagado(string $id)
    {
        try {
            $boleto = DB::table('boletos')->where('id', $id)->first();
            
            if (!$boleto) {
                return back()->with('error', 'Boleto no encontrado.');
            }

            $venta = DB::table('ventas')->where('id', $boleto->venta_id)->first();
            
            if ($venta->estado_pago === 'Pagado') {
                return back()->with('info', 'El boleto ya está pagado.');
            }
            
            DB::table('ventas')
                ->where('id', $boleto->venta_id)
                ->update([
                    'estado_pago' => 'Pagado',
                    'updated_at' => now()
                ]);
                
            return back()->with('success', 'Boleto marcado como pagado exitosamente.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar el estado: ' . $e->getMessage());
        }
    }

    /**
     * Buscar clientes por CI, nombre, apellido o teléfono
     */
    public function buscarCliente(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $clientes = DB::table('usuarios')
            ->where('rol', 'Cliente')
            ->where(function($q) use ($query) {
                $q->where('ci', 'LIKE', "%{$query}%")
                    ->orWhere('nombre', 'LIKE', "%{$query}%")
                    ->orWhere('apellido', 'LIKE', "%{$query}%")
                    ->orWhere('telefono', 'LIKE', "%{$query}%");
            })
            ->select('id', 'nombre', 'apellido', 'ci', 'telefono', 'correo')
            ->limit(10)
            ->get();

        return response()->json($clientes);
    }

    /**
     * Registro rápido de cliente desde el formulario
     */
    public function registrarCliente(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'ci' => 'required|string|max:20|unique:usuarios,ci',
            'telefono' => 'required|string|max:20',
            'correo' => 'nullable|email|max:100|unique:usuarios,correo'
        ]);

        try {
            $clienteId = DB::table('usuarios')->insertGetId([
                'nombre' => $validated['nombre'],
                'apellido' => $validated['apellido'],
                'ci' => $validated['ci'],
                'telefono' => $validated['telefono'],
                'correo' => $validated['correo'] ?? null,
                'password' => bcrypt($validated['ci']), // Password = CI
                'rol' => 'Cliente',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $cliente = DB::table('usuarios')
                ->where('id', $clienteId)
                ->select('id', 'nombre', 'apellido', 'ci', 'telefono', 'correo')
                ->first();

            return response()->json([
                'success' => true,
                'cliente' => $cliente,
                'message' => 'Cliente registrado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar cliente: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Obtener asientos ocupados para un viaje específico
     */
    public function obtenerAsientosOcupados(string $viajeId)
    {
        $asientos = DB::table('boletos')
            ->where('viaje_id', $viajeId)
            ->pluck('asiento');
            
        return response()->json($asientos);
    }

    /**
     * Verificar estado del pago QR
     */
    public function verificarEstadoPago(string $id)
    {
        try {
            $boleto = DB::table('boletos')->where('id', $id)->first();
            
            if (!$boleto) {
                return response()->json([
                    'success' => false,
                    'error' => 'Boleto no encontrado.'
                ], 404);
            }

            $pagoVenta = PagoVenta::where('venta_id', $boleto->venta_id)
                ->where('metodo_pago', 'QR')
                ->first();

            if (!$pagoVenta) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se encontró pago QR para este boleto.'
                ], 404);
            }

            // Consultar estado en PagoFácil
            $resultado = $this->pagoFacilService->consultarEstadoPago($pagoVenta);

            if (!$resultado['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $resultado['error'] ?? 'Error al consultar estado'
                ], 500);
            }

            // Verificar si el pago fue completado
            // paymentStatus: 1 o 5 = Pagado exitosamente, otro valor = Aún no pagado
            $estadoPagoFacil = $resultado['data']['values']['paymentStatus'] ?? null;
            
            // Si el estado es 1 o 5 (pagado), actualizar en BD
            if ($estadoPagoFacil == 1 || $estadoPagoFacil == 5) {
                DB::table('pago_ventas')
                    ->where('id', $pagoVenta->id)
                    ->update([
                        'estado_pago' => 'Pagado',
                        'fecha_pago' => now(),
                        'updated_at' => now()
                    ]);

                DB::table('ventas')
                    ->where('id', $boleto->venta_id)
                    ->update([
                        'estado_pago' => 'Pagado',
                        'updated_at' => now()
                    ]);

                return response()->json([
                    'success' => true,
                    'pagado' => true,
                    'message' => 'El pago ha sido confirmado exitosamente.'
                ]);
            }

            return response()->json([
                'success' => true,
                'pagado' => false,
                'message' => 'El pago aún está pendiente.',
                'estado' => $estadoPagoFacil
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al verificar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reintentar generación de QR
     */
    public function reintentarQr(string $id)
    {
        try {
            $boleto = DB::table('boletos')->where('id', $id)->first();
            
            if (!$boleto) {
                return back()->with('error', 'Boleto no encontrado.');
            }

            $pagoVenta = PagoVenta::where('venta_id', $boleto->venta_id)
                ->where('metodo_pago', 'QR')
                ->first();

            if (!$pagoVenta) {
                return back()->with('error', 'No se encontró pago QR para este boleto.');
            }

            // Generar QR nuevamente
            $resultadoQr = $this->pagoFacilService->generarQr($pagoVenta);

            if (!$resultadoQr['success']) {
                return back()->with('error', 'Error al generar QR: ' . ($resultadoQr['error'] ?? 'Error desconocido'));
            }

            return back()->with([
                'success' => 'QR regenerado exitosamente.',
                'qr_data' => [
                    'qr_base64' => $resultadoQr['pago_venta']->qr_base64,
                    'transaction_id' => $resultadoQr['pago_venta']->transaction_id,
                    'boleto_id' => $id,
                ]
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al reintentar QR: ' . $e->getMessage());
        }
    }
}
