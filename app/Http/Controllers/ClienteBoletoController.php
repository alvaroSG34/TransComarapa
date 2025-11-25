<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\RutaRepositoryInterface;
use App\Repositories\Contracts\ViajeRepositoryInterface;
use App\Services\VentaService;
use App\Services\PagoService;
use App\Services\PagoFacilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ClienteBoletoController extends Controller
{
    protected $rutaRepository;
    protected $viajeRepository;
    protected $ventaService;
    protected $pagoService;
    protected $pagoFacilService;

    public function __construct(
        RutaRepositoryInterface $rutaRepository,
        ViajeRepositoryInterface $viajeRepository,
        VentaService $ventaService,
        PagoService $pagoService,
        PagoFacilService $pagoFacilService
    ) {
        $this->rutaRepository = $rutaRepository;
        $this->viajeRepository = $viajeRepository;
        $this->ventaService = $ventaService;
        $this->pagoService = $pagoService;
        $this->pagoFacilService = $pagoFacilService;
    }

    /**
     * Mostrar todas las rutas disponibles para comprar boletos
     */
    public function mostrarRutas()
    {
        $rutas = $this->rutaRepository->all();

        return Inertia::render('Cliente/ComprarBoleto', [
            'rutas' => $rutas
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
            'viaje' => $viaje
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
        $validated = $request->validate([
            'viaje_id' => 'required|exists:viajes,id',
            'asiento' => 'required|integer|min:1',
            'metodo_pago' => 'required|in:QR', // Solo QR para clientes
        ]);

        // Obtener el usuario autenticado (cliente)
        $cliente = $request->user();
        
        if (!$cliente) {
            return back()->withErrors([
                'error' => 'Debe estar autenticado para realizar una compra.'
            ])->withInput();
        }

        // Verificar que el viaje esté disponible
        $viaje = $this->viajeRepository->find($validated['viaje_id']);
        
        if (!$viaje || $viaje->estado !== 'programado') {
            return back()->withErrors([
                'viaje_id' => 'El viaje no está disponible para venta.'
            ])->withInput();
        }

        // Validar que la fecha y hora de salida no hayan pasado
        $fechaSalida = \Carbon\Carbon::parse($viaje->fecha_salida);
        if (!$fechaSalida->isFuture()) {
            return back()->withErrors([
                'viaje_id' => 'El viaje seleccionado no está disponible porque su fecha y hora de salida ya pasaron.'
            ])->withInput();
        }

        // Verificar asientos disponibles
        $boletosVendidos = DB::table('boletos')
            ->where('viaje_id', $viaje->id)
            ->count();
        
        if ($boletosVendidos >= $viaje->asientos_totales) {
            return back()->withErrors([
                'asiento' => 'No hay asientos disponibles en este viaje.'
            ])->withInput();
        }

        // Verificar que el asiento no esté ocupado
        $asientoOcupado = DB::table('boletos')
            ->where('viaje_id', $viaje->id)
            ->where('asiento', $validated['asiento'])
            ->exists();

        if ($asientoOcupado) {
            return back()->withErrors([
                'asiento' => 'El asiento número ' . $validated['asiento'] . ' ya está ocupado. Por favor seleccione otro asiento.'
            ])->withInput();
        }

        return DB::transaction(function () use ($validated, $viaje, $cliente) {
            try {
                // Crear venta usando el servicio
                $ventaData = [
                    'fecha' => now(),
                    'monto_total' => $viaje->precio,
                    'usuario_id' => $cliente->id,
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
                $qrData = null;
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
                            'boleto_id' => $venta->boletos->first()->id,
                        ];

                        Log::info('QR generado exitosamente para cliente', [
                            'venta_id' => $venta->id,
                            'pago_venta_id' => $pagoVenta->id,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error al generar QR para cliente', [
                            'venta_id' => $venta->id ?? null,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        return back()->withErrors([
                            'qr_error' => 'Error al generar el código QR: ' . $e->getMessage() . '. Por favor, intente nuevamente.'
                        ])->withInput();
                    }
                } else {
                    // Si es efectivo, marcar como pagado
                    $venta->update(['estado_pago' => 'Pagado']);
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

                return Inertia::render('Cliente/ComprarBoletoForm', [
                    'ruta' => $ruta,
                    'viaje' => $viajeData,
                    'qr_data' => $qrData,
                    'success' => 'Boleto comprado exitosamente!'
                ]);
            } catch (\Exception $e) {
                \Log::error('Error al procesar compra: ' . $e->getMessage());
                return back()->withErrors([
                    'error' => 'Ocurrió un error al procesar la compra. Por favor, intente nuevamente.'
                ])->withInput();
            }
        });
    }
}
