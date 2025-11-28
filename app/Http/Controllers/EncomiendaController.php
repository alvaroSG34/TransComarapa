<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\RutaRepositoryInterface;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use App\Services\VentaService;
use App\Services\PagoFacilService;
use App\Services\PagoService;
use App\Models\PagoVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EncomiendaController extends Controller
{
    protected $rutaRepository;
    protected $usuarioRepository;
    protected $ventaService;
    protected $pagoFacilService;
    protected $pagoService;

    public function __construct(
        RutaRepositoryInterface $rutaRepository,
        UsuarioRepositoryInterface $usuarioRepository,
        VentaService $ventaService,
        PagoFacilService $pagoFacilService,
        PagoService $pagoService
    ) {
        $this->rutaRepository = $rutaRepository;
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
        $query = DB::table('encomiendas')
            ->join('ventas', 'encomiendas.venta_id', '=', 'ventas.id')
            ->join('rutas', 'encomiendas.ruta_id', '=', 'rutas.id')
            ->join('usuarios', 'ventas.usuario_id', '=', 'usuarios.id')
            ->select(
                'encomiendas.*',
                'ventas.monto_total',
                'ventas.estado_pago',
                'ventas.created_at as fecha_registro',
                'rutas.nombre as ruta_nombre',
                'rutas.origen',
                'rutas.destino',
                'usuarios.nombre as cliente_nombre',
                'usuarios.apellido as cliente_apellido',
                'usuarios.ci as cliente_ci',
                'usuarios.telefono as cliente_telefono'
            );

        // Filtros
        if ($request->ruta_id) {
            $query->where('encomiendas.ruta_id', $request->ruta_id);
        }

        if ($request->estado_pago) {
            $query->where('ventas.estado_pago', $request->estado_pago);
        }

        if ($request->modalidad_pago) {
            $query->where('encomiendas.modalidad_pago', $request->modalidad_pago);
        }

        if ($request->fecha_desde) {
            $query->whereDate('ventas.created_at', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->whereDate('ventas.created_at', '<=', $request->fecha_hasta);
        }

        $encomiendas = $query->orderBy('ventas.created_at', 'desc')->get();

        $rutas = $this->rutaRepository->all();

        return Inertia::render('Encomiendas/Index', [
            'encomiendas' => $encomiendas,
            'rutas' => $rutas,
            'filtros' => $request->only(['ruta_id', 'estado_pago', 'modalidad_pago', 'fecha_desde', 'fecha_hasta'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rutas = $this->rutaRepository->all();
        $clientes = $this->usuarioRepository->findByRol('Cliente');
        
        // Obtener solo viajes disponibles (programados con fecha/hora futura o en curso con fecha de llegada futura)
        $viajes = DB::table('viajes')
            ->join('rutas', 'viajes.ruta_id', '=', 'rutas.id')
            ->join('vehiculos', 'viajes.vehiculo_id', '=', 'vehiculos.id')
            ->where(function($query) {
                $query->where(function($q) {
                    // Viajes programados con fecha y hora futura
                    $q->where('viajes.estado', 'programado')
                      ->where('viajes.fecha_salida', '>', now());
                })->orWhere(function($q) {
                    // Viajes en curso pero que aún no han llegado (fecha de llegada futura)
                    $q->where('viajes.estado', 'en_curso')
                      ->where(function($subQ) {
                          $subQ->whereNull('viajes.fecha_llegada')
                               ->orWhere('viajes.fecha_llegada', '>', now());
                      });
                });
            })
            ->select(
                'viajes.id',
                'viajes.ruta_id',
                'viajes.fecha_salida',
                'viajes.precio',
                'viajes.estado',
                'viajes.vehiculo_id',
                'rutas.nombre as ruta_nombre',
                'rutas.origen',
                'rutas.destino',
                'vehiculos.marca',
                'vehiculos.modelo',
                'vehiculos.placa'
            )
            ->orderBy('viajes.fecha_salida', 'asc')
            ->get();

        return Inertia::render('Encomiendas/Create', [
            'rutas' => $rutas,
            'clientes' => $clientes,
            'viajes' => $viajes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('=== INICIO REGISTRO ENCOMIENDA ===');
        Log::info('Datos recibidos:', $request->all());
        
        try {
            // Normalizar valores vacíos a null antes de validar
            $requestData = $request->all();
            if (isset($requestData['metodo_pago']) && $requestData['metodo_pago'] === '') {
                $requestData['metodo_pago'] = null;
            }
            if (isset($requestData['metodo_pago_destino']) && $requestData['metodo_pago_destino'] === '') {
                $requestData['metodo_pago_destino'] = null;
            }
            if (isset($requestData['monto_pagado_origen']) && $requestData['monto_pagado_origen'] === '') {
                $requestData['monto_pagado_origen'] = null;
            }
            $request->merge($requestData);
            
            // Preparar reglas de validación según modalidad
            $rules = [
                'viaje_id' => 'required|exists:viajes,id',
                'ruta_id' => 'required|exists:rutas,id',
                'cliente_id' => 'required|exists:usuarios,id',
                'peso' => 'required|numeric|min:0.01',
                'descripcion' => 'nullable|string|max:500',
                'nombre_destinatario' => 'required|string|max:150',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB máximo
                'modalidad_pago' => 'required|in:origen,mixto,destino',
                'precio' => 'required|numeric|min:0',
            ];
            
            // Reglas condicionales para metodo_pago
            if (in_array($request->modalidad_pago, ['origen', 'mixto'])) {
                $rules['metodo_pago'] = 'required|in:Efectivo,QR';
            } else {
                $rules['metodo_pago'] = 'nullable|in:Efectivo,QR';
            }
            
            // metodo_pago_destino no se especifica al crear (se elige al confirmar pago en destino)
            $rules['metodo_pago_destino'] = 'nullable|in:Efectivo,QR';
            
            // Reglas condicionales para monto_pagado_origen
            if ($request->modalidad_pago === 'mixto') {
                $rules['monto_pagado_origen'] = 'required|numeric|min:0';
            } else {
                $rules['monto_pagado_origen'] = 'nullable|numeric|min:0';
            }
            
            $validated = $request->validate($rules);
            
            Log::info('Validación exitosa. Datos validados:', $validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación:', [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            throw $e;
        }

        return DB::transaction(function () use ($validated) {
            try {
                Log::info('Iniciando transacción de base de datos');
                
                // Obtener el vehiculo_id del viaje y validar disponibilidad
                Log::info('Buscando viaje con ID: ' . $validated['viaje_id']);
                $viaje = DB::table('viajes')->where('id', $validated['viaje_id'])->first();
                
                if (!$viaje) {
                    Log::error('Viaje no encontrado con ID: ' . $validated['viaje_id']);
                    return back()->withErrors([
                        'viaje_id' => 'El viaje no existe.'
                    ])->withInput();
                }
                
                // Validar que el viaje esté disponible (fecha y hora no pasadas)
                $fechaSalida = \Carbon\Carbon::parse($viaje->fecha_salida);
                $fechaLlegada = $viaje->fecha_llegada ? \Carbon\Carbon::parse($viaje->fecha_llegada) : null;
                
                $esDisponible = false;
                if ($viaje->estado === 'programado') {
                    // Viaje programado: la fecha y hora de salida deben ser futuras
                    $esDisponible = $fechaSalida->isFuture();
                } elseif ($viaje->estado === 'en_curso') {
                    // Viaje en curso: debe tener fecha de llegada futura o no tener fecha de llegada
                    $esDisponible = !$fechaLlegada || $fechaLlegada->isFuture();
                }
                
                if (!$esDisponible) {
                    Log::error('Viaje no disponible (fecha/hora pasada):', [
                        'id' => $viaje->id,
                        'estado' => $viaje->estado,
                        'fecha_salida' => $viaje->fecha_salida,
                        'fecha_llegada' => $viaje->fecha_llegada,
                        'fecha_salida_es_futura' => $fechaSalida->isFuture(),
                        'fecha_llegada_es_futura' => $fechaLlegada ? $fechaLlegada->isFuture() : 'null'
                    ]);
                    return back()->withErrors([
                        'viaje_id' => 'El viaje seleccionado no está disponible porque su fecha y hora ya pasaron. Solo se pueden registrar encomiendas en viajes con fecha y hora futura o en curso que aún no han llegado.'
                    ])->withInput();
                }
                
                Log::info('Viaje encontrado:', [
                    'id' => $viaje->id,
                    'vehiculo_id' => $viaje->vehiculo_id,
                    'ruta_id' => $viaje->ruta_id
                ]);

                // Calcular montos según modalidad
                Log::info('Calculando montos según modalidad: ' . $validated['modalidad_pago']);
                $montoPagadoOrigen = 0;
                $estadoPago = 'Pendiente';
                
                if ($validated['modalidad_pago'] === 'origen') {
                    if ($validated['metodo_pago'] === 'Efectivo') {
                        $montoPagadoOrigen = $validated['precio'];
                        $estadoPago = 'Pagado';
                        Log::info('Modalidad Origen - Efectivo: Monto pagado = ' . $montoPagadoOrigen);
                    } else {
                        // QR: se pagará después, monto_pagado_origen = 0 temporalmente
                        $montoPagadoOrigen = 0;
                        $estadoPago = 'Pendiente';
                        Log::info('Modalidad Origen - QR: Monto pendiente, se generará QR');
                    }
                } elseif ($validated['modalidad_pago'] === 'mixto') {
                    $montoPagadoOrigen = $validated['monto_pagado_origen'] ?? 0;
                    Log::info('Modalidad Mixto - Monto origen: ' . $montoPagadoOrigen . ', Método: ' . $validated['metodo_pago']);
                    if ($validated['metodo_pago'] === 'Efectivo' && $montoPagadoOrigen > 0) {
                        // Si paga en efectivo en origen, el monto ya está pagado
                        // Si es QR, monto_pagado_origen se actualizará cuando se pague
                        Log::info('Modalidad Mixto - Efectivo en origen ya pagado');
                    }
                } elseif ($validated['modalidad_pago'] === 'destino') {
                    $montoPagadoOrigen = 0;
                    $estadoPago = 'Pendiente';
                    Log::info('Modalidad Destino: Todo se pagará en destino');
                }
                
                Log::info('Montos calculados:', [
                    'monto_pagado_origen' => $montoPagadoOrigen,
                    'estado_pago' => $estadoPago
                ]);

                // Manejar subida de imagen del paquete (opcional)
                $imgUrl = null;
                if (request()->hasFile('avatar')) {
                    $file = request()->file('avatar');
                    $filename = 'encomienda-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('encomiendas', $filename, 'public');
                    $imgUrl = $path; // Solo guardar el path relativo: encomiendas/encomienda-X.png
                    Log::info('Imagen del paquete guardada:', ['path' => $imgUrl]);
                }

                // Preparar datos para el servicio
                Log::info('Preparando datos para crear venta y encomienda');
                $ventaData = [
                    'fecha' => now(),
                    'monto_total' => $validated['precio'],
                    'usuario_id' => $validated['cliente_id'],
                    'vehiculo_id' => $viaje->vehiculo_id,
                    'encomienda' => [
                        'ruta_id' => $validated['ruta_id'],
                        'viaje_id' => $validated['viaje_id'],
                        'peso' => $validated['peso'],
                        'descripcion' => $validated['descripcion'],
                        'nombre_destinatario' => $validated['nombre_destinatario'],
                        'img_url' => $imgUrl,
                        'modalidad_pago' => $validated['modalidad_pago'],
                        'metodo_pago_destino' => $validated['metodo_pago_destino'] ?? null,
                        'monto_pagado_origen' => $montoPagadoOrigen,
                    ]
                ];
                
                Log::info('Datos de venta preparados:', $ventaData);

                // Crear venta y encomienda usando el servicio
                Log::info('Llamando a ventaService->crearVentaEncomienda');
                $venta = $this->ventaService->crearVentaEncomienda($ventaData);
                Log::info('Venta creada exitosamente:', [
                    'venta_id' => $venta->id,
                    'monto_total' => $venta->monto_total
                ]);
                
                // Actualizar estado de venta si es necesario
                if ($estadoPago === 'Pagado') {
                    Log::info('Actualizando estado de venta a Pagado');
                    DB::table('ventas')->where('id', $venta->id)->update(['estado_pago' => 'Pagado']);
                }

                // Si modalidad es origen o mixto Y metodo_pago es QR, generar QR
                $qrData = null;
                Log::info('Verificando si se debe generar QR:', [
                    'modalidad_pago' => $validated['modalidad_pago'],
                    'metodo_pago' => $validated['metodo_pago'] ?? 'N/A'
                ]);
                
                if (in_array($validated['modalidad_pago'], ['origen', 'mixto']) && $validated['metodo_pago'] === 'QR') {
                    $montoQR = ($validated['modalidad_pago'] === 'origen') 
                        ? $validated['precio'] 
                        : ($validated['monto_pagado_origen'] ?? 0);
                    
                    Log::info('Se debe generar QR. Monto QR: ' . $montoQR);
                    
                    if ($montoQR > 0) {
                        Log::info('Creando PagoVenta para QR origen');
                        // Crear PagoVenta para origen (num_cuota = 1)
                        $pagoVenta = $this->pagoService->crearPago([
                            'venta_id' => $venta->id,
                            'num_cuota' => 1, // Origen
                            'monto' => $montoQR,
                            'metodo_pago' => 'QR',
                            'estado_pago' => 'Pendiente',
                        ]);
                        
                        Log::info('PagoVenta creado:', [
                            'pago_venta_id' => $pagoVenta->id,
                            'monto' => $pagoVenta->monto
                        ]);

                        // Generar QR
                        Log::info('Generando QR con PagoFácil');
                        $resultadoQr = $this->pagoFacilService->generarQr($pagoVenta);
                        
                        Log::info('Resultado generación QR:', [
                            'success' => $resultadoQr['success'] ?? false,
                            'error' => $resultadoQr['error'] ?? null
                        ]);

                        if (!$resultadoQr['success']) {
                            Log::error('Error al generar QR:', $resultadoQr);
                            throw new \Exception('Error al generar QR: ' . ($resultadoQr['error'] ?? 'Error desconocido'));
                        }

                        // Actualizar monto_pagado_origen si es origen (se actualizará cuando se pague)
                        if ($validated['modalidad_pago'] === 'origen') {
                            // No actualizar aún, se actualizará cuando se verifique el pago
                        } else {
                            // Mixto: el monto_pagado_origen ya está guardado
                        }

                        $qrData = [
                            'qr_base64' => $resultadoQr['pago_venta']->qr_base64,
                            'transaction_id' => $resultadoQr['pago_venta']->transaction_id,
                            'encomienda_id' => $venta->id,
                            'monto_total' => $montoQR,
                            'tipo' => 'origen',
                        ];
                        Log::info('QR generado exitosamente:', [
                            'transaction_id' => $qrData['transaction_id'],
                            'encomienda_id' => $qrData['encomienda_id']
                        ]);
                    } else {
                        Log::warning('Monto QR es 0 o negativo, no se genera QR');
                    }
                } else {
                    Log::info('No se requiere generar QR para esta modalidad/método de pago');
                }

                // Obtener datos para re-renderizar la vista si hay QR
                if ($qrData) {
                    Log::info('Preparando respuesta con QR. Re-renderizando vista Create');
                    $rutas = $this->rutaRepository->all();
                    $clientes = $this->usuarioRepository->findByRol('Cliente');
                    
                    $viajes = DB::table('viajes')
                        ->join('rutas', 'viajes.ruta_id', '=', 'rutas.id')
                        ->join('vehiculos', 'viajes.vehiculo_id', '=', 'vehiculos.id')
                        ->whereIn('viajes.estado', ['programado', 'en_curso'])
                        ->select(
                            'viajes.id',
                            'viajes.ruta_id',
                            'viajes.fecha_salida',
                            'viajes.precio',
                            'viajes.estado',
                            'viajes.vehiculo_id',
                            'rutas.nombre as ruta_nombre',
                            'rutas.origen',
                            'rutas.destino',
                            'vehiculos.marca',
                            'vehiculos.modelo',
                            'vehiculos.placa'
                        )
                        ->orderBy('viajes.fecha_salida', 'asc')
                        ->get();

                    Log::info('Retornando vista Create con QR');
                    return Inertia::render('Encomiendas/Create', [
                        'rutas' => $rutas,
                        'clientes' => $clientes,
                        'viajes' => $viajes,
                        'qr_data' => $qrData,
                        'success' => 'Encomienda registrada exitosamente. QR generado.'
                    ]);
                }

                Log::info('Encomienda registrada exitosamente. Redirigiendo a index');
                return redirect()->route('encomiendas.index')
                    ->with('success', 'Encomienda registrada exitosamente.');

            } catch (\Exception $e) {
                // Log del error para debugging
                Log::error('=== ERROR AL REGISTRAR ENCOMIENDA ===');
                Log::error('Mensaje: ' . $e->getMessage());
                Log::error('Archivo: ' . $e->getFile() . ':' . $e->getLine());
                Log::error('Datos validados:', $validated ?? null);
                Log::error('Trace completo:', [
                    'trace' => $e->getTraceAsString()
                ]);
                
                throw $e;
            }
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $encomienda = DB::table('encomiendas')
            ->join('ventas', 'encomiendas.venta_id', '=', 'ventas.id')
            ->join('rutas', 'encomiendas.ruta_id', '=', 'rutas.id')
            ->join('usuarios', 'ventas.usuario_id', '=', 'usuarios.id')
            ->select(
                'encomiendas.*',
                'ventas.monto_total',
                'ventas.estado_pago',
                'ventas.created_at as fecha_registro',
                'rutas.nombre as ruta_nombre',
                'rutas.origen',
                'rutas.destino',
                'usuarios.nombre as cliente_nombre',
                'usuarios.apellido as cliente_apellido',
                'usuarios.ci as cliente_ci',
                'usuarios.telefono as cliente_telefono',
                'usuarios.correo as cliente_correo'
            )
            ->where('encomiendas.venta_id', $id)
            ->first();

        if (!$encomienda) {
            return redirect()->route('encomiendas.index')
                ->with('error', 'Encomienda no encontrada.');
        }

        // Generar URL completa de la imagen (similar al accessor)
        if ($encomienda->img_url) {
            $path = $encomienda->img_url;
            
            // Si no es una URL completa, construirla
            if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
                // Si tiene /storage/ al inicio, quitarlo
                if (str_starts_with($path, '/storage/')) {
                    $path = substr($path, 9);
                }
                $encomienda->img_url_full = url('storage/' . $path);
            } else {
                $encomienda->img_url_full = $path;
            }
        } else {
            $encomienda->img_url_full = null;
        }

        // Obtener PagoVenta de origen (num_cuota = 1) si existe
        $pagoOrigen = PagoVenta::where('venta_id', $id)
            ->where('num_cuota', 1)
            ->where('metodo_pago', 'QR')
            ->first();

        // Obtener PagoVenta de destino (num_cuota = 2) si existe
        $pagoDestino = PagoVenta::where('venta_id', $id)
            ->where('num_cuota', 2)
            ->where('metodo_pago', 'QR')
            ->first();

        return Inertia::render('Encomiendas/Show', [
            'encomienda' => $encomienda,
            'pago_origen' => $pagoOrigen ? [
                'id' => $pagoOrigen->id,
                'qr_base64' => $pagoOrigen->qr_base64,
                'transaction_id' => $pagoOrigen->transaction_id,
                'estado_pago' => $pagoOrigen->estado_pago,
                'monto' => $pagoOrigen->monto,
            ] : null,
            'pago_destino' => $pagoDestino ? [
                'id' => $pagoDestino->id,
                'qr_base64' => $pagoDestino->qr_base64,
                'transaction_id' => $pagoDestino->transaction_id,
                'estado_pago' => $pagoDestino->estado_pago,
                'monto' => $pagoDestino->monto,
            ] : null,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $encomienda = DB::table('encomiendas')
            ->join('ventas', 'encomiendas.venta_id', '=', 'ventas.id')
            ->select(
                'encomiendas.*', 
                'ventas.monto_total as precio', 
                'ventas.usuario_id as cliente_id', 
                'ventas.estado_pago'
            )
            ->where('encomiendas.venta_id', $id)
            ->first();

        if (!$encomienda) {
            return redirect()->route('encomiendas.index')
                ->with('error', 'Encomienda no encontrada.');
        }

        $rutas = $this->rutaRepository->all();
        $clientes = $this->usuarioRepository->findByRol('Cliente');
        
        // Obtener solo viajes disponibles (programados con fecha/hora futura o en curso con fecha de llegada futura)
        $viajes = DB::table('viajes')
            ->join('rutas', 'viajes.ruta_id', '=', 'rutas.id')
            ->join('vehiculos', 'viajes.vehiculo_id', '=', 'vehiculos.id')
            ->where(function($query) {
                $query->where(function($q) {
                    // Viajes programados con fecha y hora futura
                    $q->where('viajes.estado', 'programado')
                      ->where('viajes.fecha_salida', '>', now());
                })->orWhere(function($q) {
                    // Viajes en curso pero que aún no han llegado (fecha de llegada futura)
                    $q->where('viajes.estado', 'en_curso')
                      ->where(function($subQ) {
                          $subQ->whereNull('viajes.fecha_llegada')
                               ->orWhere('viajes.fecha_llegada', '>', now());
                      });
                });
            })
            ->select(
                'viajes.id',
                'viajes.ruta_id',
                'viajes.fecha_salida',
                'viajes.precio',
                'viajes.estado',
                'viajes.vehiculo_id',
                'rutas.nombre as ruta_nombre',
                'rutas.origen',
                'rutas.destino',
                'vehiculos.marca',
                'vehiculos.modelo',
                'vehiculos.placa'
            )
            ->orderBy('viajes.fecha_salida', 'asc')
            ->get();

        // Generar URL completa de la imagen (similar al accessor)
        if ($encomienda->img_url) {
            $path = $encomienda->img_url;
            
            // Si no es una URL completa, construirla
            if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
                // Si tiene /storage/ al inicio, quitarlo
                if (str_starts_with($path, '/storage/')) {
                    $path = substr($path, 9);
                }
                $encomienda->img_url_full = url('storage/' . $path);
            } else {
                $encomienda->img_url_full = $path;
            }
        } else {
            $encomienda->img_url_full = null;
        }

        return Inertia::render('Encomiendas/Edit', [
            'encomienda' => $encomienda,
            'rutas' => $rutas,
            'clientes' => $clientes,
            'viajes' => $viajes
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'viaje_id' => 'required|exists:viajes,id',
            'ruta_id' => 'required|exists:rutas,id',
            'cliente_id' => 'required|exists:usuarios,id',
            'peso' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:500',
            'nombre_destinatario' => 'required|string|max:150',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB máximo
            'modalidad_pago' => 'required|in:origen,mixto,destino',
            'precio' => 'required|numeric|min:0',
            'monto_pagado_origen' => 'required_if:modalidad_pago,mixto|nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            
            // Obtener el vehiculo_id del viaje
            $viaje = DB::table('viajes')->where('id', $validated['viaje_id'])->first();
            
            // Calcular montos según modalidad de pago
            $montoPagadoOrigen = 0;
            $estadoPago = 'Pendiente';
            
            if ($validated['modalidad_pago'] === 'origen') {
                $montoPagadoOrigen = $validated['precio'];
                $estadoPago = 'Pagado';
            } elseif ($validated['modalidad_pago'] === 'mixto') {
                $montoPagadoOrigen = $validated['monto_pagado_origen'] ?? 0;
            } elseif ($validated['modalidad_pago'] === 'destino') {
                $montoPagadoOrigen = 0;
            }

            // Manejar subida de imagen del paquete (opcional)
            $imgUrl = null;
            $encomienda = DB::table('encomiendas')->where('venta_id', $id)->first();
            
            if (request()->hasFile('avatar')) {
                // Eliminar imagen anterior si existe
                if ($encomienda && $encomienda->img_url) {
                    // Limpiar el path por si tiene /storage/ (compatibilidad con registros antiguos)
                    $oldPath = $encomienda->img_url;
                    if (str_starts_with($oldPath, '/storage/')) {
                        $oldPath = substr($oldPath, 9); // Remover '/storage/'
                    }
                    $oldPath = ltrim($oldPath, '/');
                    
                    if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                // Guardar nueva imagen
                $file = request()->file('avatar');
                $filename = 'encomienda-' . $id . '-' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('encomiendas', $filename, 'public');
                $imgUrl = $path; // Solo guardar el path relativo: encomiendas/encomienda-X.png
            } else {
                // Mantener la imagen actual si no se sube una nueva
                $imgUrl = $encomienda->img_url ?? null;
            }

            // Actualizar encomienda
            DB::table('encomiendas')
                ->where('venta_id', $id)
                ->update([
                    'ruta_id' => $validated['ruta_id'],
                    'viaje_id' => $validated['viaje_id'],
                    'peso' => $validated['peso'],
                    'descripcion' => $validated['descripcion'],
                    'nombre_destinatario' => $validated['nombre_destinatario'],
                    'img_url' => $imgUrl,
                    'modalidad_pago' => $validated['modalidad_pago'],
                    'monto_pagado_origen' => $montoPagadoOrigen,
                    'updated_at' => now()
                ]);

            // Actualizar monto y cliente de la venta
            DB::table('ventas')
                ->where('id', $id)
                ->update([
                    'usuario_id' => $validated['cliente_id'],
                    'monto_total' => $validated['precio'],
                    'vehiculo_id' => $viaje->vehiculo_id,
                    'estado_pago' => $estadoPago,
                    'updated_at' => now()
                ]);

            DB::commit();

            return redirect()->route('encomiendas.index')
                ->with('success', 'Encomienda actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la encomienda: ' . $e->getMessage());
        }
    }

    /**
     * Registrar pago en destino (confirma el pago completo del monto pendiente)
     */
    public function registrarPagoDestino(Request $request, string $id)
    {
        Log::info('=== REGISTRAR PAGO DESTINO ===');
        Log::info('Encomienda ID:', ['id' => $id]);
        Log::info('Datos recibidos:', $request->all());
        
        $validated = $request->validate([
            'metodo_pago_destino' => 'required|in:Efectivo,QR'
        ]);
        
        Log::info('Validación exitosa. Método:', ['metodo_pago_destino' => $validated['metodo_pago_destino']]);

        try {
            DB::beginTransaction();

            $encomienda = DB::table('encomiendas')
                ->join('ventas', 'encomiendas.venta_id', '=', 'ventas.id')
                ->select('encomiendas.*', 'ventas.monto_total', 'ventas.estado_pago')
                ->where('encomiendas.venta_id', $id)
                ->first();

            if (!$encomienda) {
                throw new \Exception('Encomienda no encontrada.');
            }

            // Calcular el monto pendiente
            $montoPendiente = $encomienda->monto_total - $encomienda->monto_pagado_origen - $encomienda->monto_pagado_destino;
            
            // Verificar que haya monto pendiente
            if ($montoPendiente <= 0) {
                throw new \Exception('Esta encomienda ya está completamente pagada.');
            }

            // Actualizar metodo_pago_destino en la encomienda
            DB::table('encomiendas')
                ->where('venta_id', $id)
                ->update([
                    'metodo_pago_destino' => $validated['metodo_pago_destino'],
                    'updated_at' => now()
                ]);

            // Si metodo_pago_destino es QR, generar QR
            if ($validated['metodo_pago_destino'] === 'QR') {
                // Crear PagoVenta para destino (num_cuota = 2)
                $pagoVenta = $this->pagoService->crearPago([
                    'venta_id' => $id,
                    'num_cuota' => 2, // Destino
                    'monto' => $montoPendiente,
                    'metodo_pago' => 'QR',
                    'estado_pago' => 'Pendiente',
                ]);

                // Generar QR
                $resultadoQr = $this->pagoFacilService->generarQr($pagoVenta);

                if (!$resultadoQr['success']) {
                    throw new \Exception('Error al generar QR: ' . ($resultadoQr['error'] ?? 'Error desconocido'));
                }

                DB::commit();

                return redirect()->route('encomiendas.show', $id)
                    ->with('qr_data_destino', [
                        'qr_base64' => $resultadoQr['pago_venta']->qr_base64,
                        'transaction_id' => $resultadoQr['pago_venta']->transaction_id,
                        'encomienda_id' => $id,
                        'monto_total' => $montoPendiente,
                        'tipo' => 'destino',
                    ])
                    ->with('success', 'QR generado para pago en destino. Monto pendiente: Bs ' . number_format($montoPendiente, 2) . '.');
            }

            // Si es Efectivo, comportamiento normal
            $nuevoMontoPagadoDestino = $encomienda->monto_pagado_destino + $montoPendiente;
            
            DB::table('encomiendas')
                ->where('venta_id', $id)
                ->update([
                    'monto_pagado_destino' => $nuevoMontoPagadoDestino,
                    'updated_at' => now()
                ]);

            // Marcar la venta como pagada
            DB::table('ventas')
                ->where('id', $id)
                ->update([
                    'estado_pago' => 'Pagado',
                    'updated_at' => now()
                ]);

            DB::commit();

            return redirect()->route('encomiendas.show', $id)
                ->with('success', 'Pago en destino confirmado exitosamente por Bs ' . number_format($montoPendiente, 2) . '.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Verificar estado del pago QR (origen o destino)
     */
    public function verificarEstadoPago(Request $request, string $id)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:origen,destino',
        ]);

        try {
            $encomienda = DB::table('encomiendas')->where('venta_id', $id)->first();
            
            if (!$encomienda) {
                return response()->json([
                    'success' => false,
                    'error' => 'Encomienda no encontrada.'
                ], 404);
            }

            // Buscar PagoVenta según tipo
            $numCuota = $validated['tipo'] === 'origen' ? 1 : 2;
            
            $pagoVenta = PagoVenta::where('venta_id', $id)
                ->where('num_cuota', $numCuota)
                ->where('metodo_pago', 'QR')
                ->first();

            if (!$pagoVenta) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se encontró pago QR ' . $validated['tipo'] . ' para esta encomienda.'
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
            // State: 1=Pendiente, 2=Pagado, 3=Anulado, 4=Vencido, 5=Validación Pendiente
            // También puede venir como 'paymentStatus' en algunos casos
            $estadoPagoFacil = $resultado['data']['values']['State'] 
                ?? $resultado['data']['values']['paymentStatus'] 
                ?? $resultado['data']['values']['state'] 
                ?? null;
            
            // Estados 2 o 5 significan "Pagado"
            if ($estadoPagoFacil == 2 || $estadoPagoFacil == 5) {
                DB::table('pago_ventas')
                    ->where('id', $pagoVenta->id)
                    ->update([
                        'estado_pago' => 'Pagado',
                        'fecha_pago' => now(),
                        'updated_at' => now()
                    ]);

                // Actualizar monto pagado según tipo
                if ($validated['tipo'] === 'origen') {
                    DB::table('encomiendas')
                        ->where('venta_id', $id)
                        ->update([
                            'monto_pagado_origen' => $pagoVenta->monto,
                            'updated_at' => now()
                        ]);
                } else {
                    $encomiendaActual = DB::table('encomiendas')
                        ->where('venta_id', $id)
                        ->first();
                    
                    DB::table('encomiendas')
                        ->where('venta_id', $id)
                        ->update([
                            'monto_pagado_destino' => ($encomiendaActual->monto_pagado_destino ?? 0) + $pagoVenta->monto,
                            'updated_at' => now()
                        ]);
                }

                // Verificar si la venta está completamente pagada
                $encomiendaActualizada = DB::table('encomiendas')
                    ->where('venta_id', $id)
                    ->first();
                
                $totalPagado = $encomiendaActualizada->monto_pagado_origen + $encomiendaActualizada->monto_pagado_destino;
                $montoTotal = DB::table('ventas')->where('id', $id)->value('monto_total');
                
                if ($totalPagado >= $montoTotal) {
                    DB::table('ventas')
                        ->where('id', $id)
                        ->update([
                            'estado_pago' => 'Pagado',
                            'updated_at' => now()
                        ]);
                }

                return response()->json([
                    'success' => true,
                    'pagado' => true,
                    'message' => 'El pago ' . $validated['tipo'] . ' ha sido confirmado exitosamente.'
                ]);
            }

            return response()->json([
                'success' => true,
                'pagado' => false,
                'message' => 'El pago ' . $validated['tipo'] . ' aún está pendiente.',
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
     * Reintentar generación de QR (origen o destino)
     */
    public function reintentarQr(Request $request, string $id)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:origen,destino',
        ]);

        try {
            $encomienda = DB::table('encomiendas')->where('venta_id', $id)->first();
            
            if (!$encomienda) {
                return back()->with('error', 'Encomienda no encontrada.');
            }

            // Buscar PagoVenta según tipo
            $numCuota = $validated['tipo'] === 'origen' ? 1 : 2;
            
            $pagoVenta = PagoVenta::where('venta_id', $id)
                ->where('num_cuota', $numCuota)
                ->where('metodo_pago', 'QR')
                ->first();

            if (!$pagoVenta) {
                return back()->with('error', 'No se encontró pago QR ' . $validated['tipo'] . ' para esta encomienda.');
            }

            // Generar QR nuevamente
            $resultadoQr = $this->pagoFacilService->generarQr($pagoVenta);

            if (!$resultadoQr['success']) {
                return back()->with('error', 'Error al generar QR: ' . ($resultadoQr['error'] ?? 'Error desconocido'));
            }

            $qrDataKey = $validated['tipo'] === 'origen' ? 'qr_data' : 'qr_data_destino';

            return back()->with([
                'success' => 'QR ' . $validated['tipo'] . ' regenerado exitosamente.',
                $qrDataKey => [
                    'qr_base64' => $resultadoQr['pago_venta']->qr_base64,
                    'transaction_id' => $resultadoQr['pago_venta']->transaction_id,
                    'encomienda_id' => $id,
                    'monto_total' => $pagoVenta->monto,
                    'tipo' => $validated['tipo'],
                ]
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al reintentar QR: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $encomienda = DB::table('encomiendas')->where('venta_id', $id)->first();

            if (!$encomienda) {
                throw new \Exception('Encomienda no encontrada.');
            }

            // Obtener el venta_id antes de eliminar
            $ventaId = $encomienda->venta_id;

            // Eliminar encomienda
            DB::table('encomiendas')->where('venta_id', $id)->delete();

            // Eliminar la venta asociada
            // Esto también eliminará automáticamente los pago_ventas asociados por cascade
            DB::table('ventas')->where('id', $ventaId)->delete();

            DB::commit();

            return redirect()->route('encomiendas.index')
                ->with('success', 'Encomienda y venta asociada eliminadas exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Error al eliminar la encomienda: ' . $e->getMessage());
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
}
