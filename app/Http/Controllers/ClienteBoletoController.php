<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\RutaRepositoryInterface;
use App\Repositories\Contracts\ViajeRepositoryInterface;
use App\Services\VentaService;
use App\Services\PagoService;
use App\Services\PagoFacilService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ClienteBoletoController extends Controller
{
    protected $rutaRepository;
    protected $viajeRepository;
    protected $ventaService;
    protected $pagoService;
    protected $pagoFacilService;
    protected $stripeService;

    public function __construct(
        RutaRepositoryInterface $rutaRepository,
        ViajeRepositoryInterface $viajeRepository,
        VentaService $ventaService,
        PagoService $pagoService,
        PagoFacilService $pagoFacilService,
        StripeService $stripeService
    ) {
        $this->rutaRepository = $rutaRepository;
        $this->viajeRepository = $viajeRepository;
        $this->ventaService = $ventaService;
        $this->pagoService = $pagoService;
        $this->pagoFacilService = $pagoFacilService;
        $this->stripeService = $stripeService;
    }

    /**
     * Mostrar todas las rutas disponibles para comprar boletos
     */
    public function mostrarRutas(Request $request)
    {
        // Obtener el país del usuario autenticado
        $usuario = $request->user();
        $paisUsuario = $usuario->pais ?? 'Bolivia';
        
        // Obtener filtro de país desde request o usar el del usuario
        $paisFiltro = $request->input('pais', $paisUsuario);
        
        // Obtener todas las rutas
        $rutas = $this->rutaRepository->all();
        
        // Filtrar por país si se especifica
        if ($paisFiltro && $paisFiltro !== 'todos') {
            $rutas = $rutas->filter(function($ruta) use ($paisFiltro) {
                return $ruta->pais_operacion === $paisFiltro;
            })->values();
        }
        
        // Obtener lista única de países disponibles
        $paisesDisponibles = $this->rutaRepository->all()
            ->pluck('pais_operacion')
            ->unique()
            ->sort()
            ->values();

        return Inertia::render('Cliente/ComprarBoleto', [
            'rutas' => $rutas,
            'paisesDisponibles' => $paisesDisponibles,
            'paisSeleccionado' => $paisFiltro,
            'paisUsuario' => $paisUsuario
        ]);
    }

    /**
     * Mostrar viajes disponibles de una ruta específica
     */
    public function mostrarViajes(Request $request, $rutaId)
    {
        // Verificar que la ruta existe
        $ruta = $this->rutaRepository->find($rutaId);
        
        if (!$ruta) {
            return redirect()->route('cliente.boletos.comprar')
                ->with('error', 'Ruta no encontrada.');
        }

        // Obtener solo viajes disponibles (programados con fecha/hora futura y con asientos disponibles)
        $viajes = DB::table('viajes')
            ->join('rutas', 'viajes.ruta_id', '=', 'rutas.id')
            ->join('vehiculos', 'viajes.vehiculo_id', '=', 'vehiculos.id')
            ->where('viajes.ruta_id', $rutaId)
            ->where('viajes.estado', 'programado')
            ->where('viajes.fecha_salida', '>', now())
            ->select(
                'viajes.id',
                'viajes.ruta_id',
                'viajes.fecha_salida',
                'viajes.fecha_llegada',
                'viajes.precio',
                'viajes.moneda',
                'viajes.asientos_totales',
                'viajes.estado',
                'rutas.nombre as ruta_nombre',
                'rutas.origen',
                'rutas.destino',
                'vehiculos.marca',
                'vehiculos.modelo',
                'vehiculos.placa'
            )
            ->orderBy('viajes.fecha_salida', 'asc')
            ->get()
            ->map(function ($viaje) {
                // Calcular asientos disponibles
                $boletosVendidos = DB::table('boletos')
                    ->where('viaje_id', $viaje->id)
                    ->count();
                
                $viaje->asientos_disponibles = $viaje->asientos_totales - $boletosVendidos;
                $viaje->tiene_asientos = $viaje->asientos_disponibles > 0;
                
                return $viaje;
            })
            ->filter(function ($viaje) {
                // Solo mostrar viajes con asientos disponibles
                return $viaje->tiene_asientos;
            })
            ->values();

        return Inertia::render('Cliente/SeleccionarViaje', [
            'ruta' => $ruta,
            'viajes' => $viajes
        ]);
    }

    /**
     * Mostrar formulario de compra para un viaje específico
     */
    public function mostrarFormularioCompra(Request $request, $viajeId, $rutaId)
    {
        // Verificar que la ruta existe
        $ruta = $this->rutaRepository->find($rutaId);
        
        if (!$ruta) {
            return redirect()->route('cliente.boletos.comprar')
                ->with('error', 'Ruta no encontrada.');
        }

        // Obtener el viaje con toda su información
        $viaje = DB::table('viajes')
            ->join('rutas', 'viajes.ruta_id', '=', 'rutas.id')
            ->join('vehiculos', 'viajes.vehiculo_id', '=', 'vehiculos.id')
            ->where('viajes.id', $viajeId)
            ->where('viajes.ruta_id', $rutaId)
            ->where('viajes.estado', 'programado')
            ->where('viajes.fecha_salida', '>', now())
            ->select(
                'viajes.id',
                'viajes.ruta_id',
                'viajes.fecha_salida',
                'viajes.fecha_llegada',
                'viajes.precio',
                'viajes.moneda',
                'viajes.asientos_totales',
                'viajes.estado',
                'viajes.vehiculo_id',
                'rutas.nombre as ruta_nombre',
                'rutas.origen',
                'rutas.destino',
                'vehiculos.marca',
                'vehiculos.modelo',
                'vehiculos.placa'
            )
            ->first();

        if (!$viaje) {
            return redirect()->route('cliente.boletos.viajes', $rutaId)
                ->with('error', 'Viaje no encontrado o no disponible.');
        }

        // Calcular asientos disponibles
        $boletosVendidos = DB::table('boletos')
            ->where('viaje_id', $viaje->id)
            ->count();
        
        $viaje->asientos_disponibles = $viaje->asientos_totales - $boletosVendidos;

        if ($viaje->asientos_disponibles <= 0) {
            return redirect()->route('cliente.boletos.viajes', $rutaId)
                ->with('error', 'No hay asientos disponibles para este viaje.');
        }

        return Inertia::render('Cliente/ComprarBoletoForm', [
            'ruta' => $ruta,
            'viaje' => $viaje,
            'stripe_key' => config('services.stripe.key'),
        ]);
    }

    /**
     * Obtener asientos ocupados para un viaje (para clientes)
     */
    public function obtenerAsientosOcupados($viajeId)
    {
        // Verificar que el viaje existe y está disponible
        $viaje = DB::table('viajes')
            ->where('id', $viajeId)
            ->where('estado', 'programado')
            ->where('fecha_salida', '>', now())
            ->first();

        if (!$viaje) {
            return response()->json(['error' => 'Viaje no encontrado o no disponible'], 404);
        }

        $asientos = DB::table('boletos')
            ->where('viaje_id', $viajeId)
            ->pluck('asiento');
            
        return response()->json($asientos);
    }

    /**
     * Procesar la compra del boleto
     */
    public function procesarCompra(Request $request)
    {
        // Si es un GET (F5 después de POST), limpiar ventas pendientes recientes y redirigir
        if ($request->isMethod('GET')) {
            // Eliminar ventas pendientes de Stripe del usuario creadas en los últimos 15 minutos
            // que no han sido pagadas (evitar que bloqueen asientos)
            $ventasPendientes = DB::table('ventas')
                ->join('boletos', 'ventas.id', '=', 'boletos.venta_id')
                ->join('pago_ventas', 'ventas.id', '=', 'pago_ventas.venta_id')
                ->where('ventas.usuario_id', $request->user()->id)
                ->where('ventas.estado_pago', 'Pendiente')
                ->where('pago_ventas.metodo_pago', 'Stripe')
                ->where('ventas.created_at', '>=', now()->subMinutes(15))
                ->select('ventas.id', 'boletos.id as boleto_id', 'pago_ventas.id as pago_id')
                ->get();

            foreach ($ventasPendientes as $venta) {
                // Eliminar en orden: PagoVenta -> Boleto -> Venta
                DB::table('pago_ventas')->where('id', $venta->pago_id)->delete();
                DB::table('boletos')->where('id', $venta->boleto_id)->delete();
                DB::table('ventas')->where('id', $venta->id)->delete();
            }

            return redirect()->route('cliente.boletos.comprar')
                ->with('info', 'La sesión de compra expiró. El asiento ha sido liberado. Por favor, inicie el proceso nuevamente.');
        }

        $validated = $request->validate([
            'viaje_id' => 'required|exists:viajes,id',
            'asientos' => 'required|array|min:1',
            'asientos.*' => 'required|integer|min:1',
            'metodo_pago' => 'required|in:QR,Stripe',
        ]);

        // Validar que no haya más de 10 asientos en una compra
        if (count($validated['asientos']) > 10) {
            return back()->withErrors([
                'asientos' => 'No puede comprar más de 10 boletos en una sola transacción.'
            ])->withInput();
        }

        // Lock atómico para todos los asientos
        $locks = [];
        foreach ($validated['asientos'] as $asiento) {
            $lockKey = "asiento_compra_{$validated['viaje_id']}_{$asiento}";
            $lock = Cache::lock($lockKey, 10);
            
            if (!$lock->get()) {
                // Liberar locks ya adquiridos
                foreach ($locks as $acquiredLock) {
                    $acquiredLock->release();
                }
                
                return back()->withErrors([
                    'asientos' => 'Uno de los asientos seleccionados está siendo procesado por otro usuario. Por favor, intente nuevamente.'
                ])->withInput();
            }
            
            $locks[] = $lock;
        }

        try {
            // Obtener el usuario autenticado (cliente)
            $cliente = $request->user();
            
            if (!$cliente) {
                foreach ($locks as $lock) {
                    $lock->release();
                }
                return back()->withErrors([
                    'error' => 'Debe estar autenticado para realizar una compra.'
                ])->withInput();
            }

            // Verificar que el viaje esté disponible
            $viaje = $this->viajeRepository->find($validated['viaje_id']);
            
            if (!$viaje || $viaje->estado !== 'programado') {
                foreach ($locks as $lock) {
                    $lock->release();
                }
                return back()->withErrors([
                    'viaje_id' => 'El viaje no está disponible para venta.'
                ])->withInput();
            }

            // Validar que la fecha y hora de salida no hayan pasado
            $fechaSalida = \Carbon\Carbon::parse($viaje->fecha_salida);
            if (!$fechaSalida->isFuture()) {
                foreach ($locks as $lock) {
                    $lock->release();
                }
                return back()->withErrors([
                    'viaje_id' => 'El viaje seleccionado no está disponible porque su fecha y hora de salida ya pasaron.'
                ])->withInput();
            }

            // Verificar cantidad de asientos disponibles
            $boletosVendidos = DB::table('boletos')
                ->where('viaje_id', $viaje->id)
                ->count();
            
            $asientosDisponibles = $viaje->asientos_totales - $boletosVendidos;
            
            if ($asientosDisponibles < count($validated['asientos'])) {
                foreach ($locks as $lock) {
                    $lock->release();
                }
                return back()->withErrors([
                    'asientos' => "Solo hay {$asientosDisponibles} asiento(s) disponible(s) en este viaje."
                ])->withInput();
            }

            // Verificar que ninguno de los asientos esté ocupado
            $asientosOcupados = DB::table('boletos')
                ->where('viaje_id', $viaje->id)
                ->whereIn('asiento', $validated['asientos'])
                ->pluck('asiento')
                ->toArray();

            if (count($asientosOcupados) > 0) {
                foreach ($locks as $lock) {
                    $lock->release();
                }
                $asientosStr = implode(', ', $asientosOcupados);
                return back()->withErrors([
                    'asientos' => "Los siguientes asientos ya están ocupados: {$asientosStr}. Por favor seleccione otros asientos."
                ])->withInput();
            }

            return DB::transaction(function () use ($validated, $viaje, $cliente, $locks) {
                // Preparar array de boletos
                $boletos = [];
                foreach ($validated['asientos'] as $asiento) {
                    $boletos[] = [
                        'asiento' => $asiento,
                        'ruta_id' => $viaje->ruta_id,
                        'viaje_id' => $viaje->id
                    ];
                }
                
                // Calcular monto total (precio unitario * cantidad de asientos)
                $montoTotal = $viaje->precio * count($validated['asientos']);
                
                // Crear venta usando el servicio
                $ventaData = [
                    'fecha' => now(),
                    'monto_total' => $montoTotal,
                    'usuario_id' => $cliente->id,
                    'vehiculo_id' => $viaje->vehiculo_id,
                    'boletos' => $boletos
                ];

                $venta = $this->ventaService->crearVentaBoleto($ventaData);

                // Datos de pago según método
                $qrData = null;
                $stripeData = null;

                if ($validated['metodo_pago'] === 'QR') {
                    try {
                        // Crear PagoVenta usando el servicio
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

                        // Preparar datos del QR para la vista
                        $qrData = [
                            'qr_base64' => $resultadoQr['pago_venta']->qr_base64,
                            'transaction_id' => $resultadoQr['pago_venta']->transaction_id,
                            'boleto_ids' => $venta->boletos->pluck('id')->toArray(),
                        ];

                        Log::info('QR generado exitosamente para cliente', [
                            'venta_id' => $venta->id,
                            'pago_venta_id' => $pagoVenta->id,
                            'cantidad_boletos' => count($venta->boletos),
                        ]);
                    } catch (\Exception $e) {
                        // Liberar todos los locks en caso de error
                        foreach ($locks as $lock) {
                            $lock->release();
                        }
                        
                        Log::error('Error al generar QR para cliente', [
                            'venta_id' => $venta->id ?? null,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        return back()->withErrors([
                            'qr_error' => 'Error al generar el código QR: ' . $e->getMessage() . '. Por favor, intente nuevamente.'
                        ])->withInput();
                    }
                } elseif ($validated['metodo_pago'] === 'Stripe') {
                    try {
                        // Usar la moneda del viaje (configurada por el administrador)
                        $moneda = $viaje->moneda ?? 'BOB';
                        
                        // Crear PagoVenta para Stripe
                        $pagoVenta = $this->pagoService->crearPago([
                            'venta_id' => $venta->id,
                            'num_cuota' => 1,
                            'monto' => $venta->monto_total,
                            'metodo_pago' => 'Stripe',
                            'estado_pago' => 'Pendiente',
                            'moneda' => $moneda,
                        ]);

                        // Crear PaymentIntent
                        $resultadoStripe = $this->stripeService->crearPaymentIntent($pagoVenta, $moneda);

                        if (!$resultadoStripe['success']) {
                            throw new \Exception('Error al crear PaymentIntent: ' . ($resultadoStripe['error'] ?? 'Error desconocido'));
                        }

                        // Preparar datos de Stripe para la vista
                        $stripeData = [
                            'client_secret' => $resultadoStripe['client_secret'],
                            'payment_intent_id' => $resultadoStripe['payment_intent']->id,
                            'amount' => $resultadoStripe['payment_intent']->amount,
                            'currency' => $resultadoStripe['payment_intent']->currency,
                            'boleto_ids' => $venta->boletos->pluck('id')->toArray(),
                        ];

                        Log::info('PaymentIntent creado exitosamente para cliente', [
                            'venta_id' => $venta->id,
                            'pago_venta_id' => $pagoVenta->id,
                            'payment_intent_id' => $resultadoStripe['payment_intent']->id,
                            'cantidad_boletos' => count($venta->boletos),
                        ]);
                    } catch (\Exception $e) {
                        // Liberar todos los locks en caso de error
                        foreach ($locks as $lock) {
                            $lock->release();
                        }
                        
                        Log::error('Error al crear PaymentIntent para cliente', [
                            'venta_id' => $venta->id ?? null,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        return back()->withErrors([
                            'stripe_error' => 'Error al inicializar pago con Stripe: ' . $e->getMessage() . '. Por favor, intente nuevamente.'
                        ])->withInput();
                    }
                }

                // Obtener datos del viaje y ruta para re-renderizar
                $ruta = $this->rutaRepository->find($viaje->ruta_id);
                $viajeData = DB::table('viajes')
                    ->join('rutas', 'viajes.ruta_id', '=', 'rutas.id')
                    ->join('vehiculos', 'viajes.vehiculo_id', '=', 'vehiculos.id')
                    ->where('viajes.id', $viaje->id)
                    ->select(
                        'viajes.id',
                        'viajes.ruta_id',
                        'viajes.fecha_salida',
                        'viajes.fecha_llegada',
                        'viajes.precio',
                        'viajes.moneda',
                        'viajes.asientos_totales',
                        'viajes.estado',
                        'viajes.vehiculo_id',
                        'rutas.nombre as ruta_nombre',
                        'rutas.origen',
                        'rutas.destino',
                        'vehiculos.marca',
                        'vehiculos.modelo',
                        'vehiculos.placa'
                    )
                    ->first();

                $boletosVendidos = DB::table('boletos')
                    ->where('viaje_id', $viaje->id)
                    ->count();
                
                $viajeData->asientos_disponibles = $viajeData->asientos_totales - $boletosVendidos;

                // Liberar todos los locks después de completar la transacción exitosamente
                foreach ($locks as $lock) {
                    $lock->release();
                }

                $cantidadBoletos = count($validated['asientos']);
                $mensaje = $cantidadBoletos === 1 
                    ? '¡Boleto comprado exitosamente!' 
                    : "¡{$cantidadBoletos} boletos comprados exitosamente!";
                
                return Inertia::render('Cliente/ComprarBoletoForm', [
                    'ruta' => $ruta,
                    'viaje' => $viajeData,
                    'qr_data' => $qrData,
                    'stripe_data' => $stripeData,
                    'stripe_key' => config('services.stripe.key'),
                    'success' => $mensaje
                ]);
            });
        } catch (\Exception $e) {
            // Liberar todos los locks en caso de error
            foreach ($locks as $lock) {
                $lock->release();
            }
            
            \Log::error('Error al procesar compra: ' . $e->getMessage());
            return back()->withErrors([
                'error' => 'Ocurrió un error al procesar la compra. Por favor, intente nuevamente.'
            ])->withInput();
        }
    }
}
