<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\RutaRepositoryInterface;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use App\Services\VentaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EncomiendaController extends Controller
{
    protected $rutaRepository;
    protected $usuarioRepository;
    protected $ventaService;

    public function __construct(
        RutaRepositoryInterface $rutaRepository,
        UsuarioRepositoryInterface $usuarioRepository,
        VentaService $ventaService
    ) {
        $this->rutaRepository = $rutaRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->ventaService = $ventaService;
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
        
        // Obtener viajes programados o en curso
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
        $validated = $request->validate([
            'viaje_id' => 'required|exists:viajes,id',
            'ruta_id' => 'required|exists:rutas,id',
            'cliente_id' => 'required|exists:usuarios,id',
            'peso' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:500',
            'nombre_destinatario' => 'required|string|max:150',
            'img_url' => 'nullable|url|max:255',
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

            // Crear venta
            $ventaId = DB::table('ventas')->insertGetId([
                'usuario_id' => $validated['cliente_id'],
                'monto_total' => $validated['precio'],
                'tipo' => 'Encomienda',
                'estado_pago' => $estadoPago,
                'fecha' => now(),
                'vehiculo_id' => $viaje->vehiculo_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Crear encomienda
            DB::table('encomiendas')->insert([
                'venta_id' => $ventaId,
                'ruta_id' => $validated['ruta_id'],
                'viaje_id' => $validated['viaje_id'],
                'peso' => $validated['peso'],
                'descripcion' => $validated['descripcion'],
                'nombre_destinatario' => $validated['nombre_destinatario'],
                'img_url' => $validated['img_url'],
                'modalidad_pago' => $validated['modalidad_pago'],
                'monto_pagado_origen' => $montoPagadoOrigen,
                'monto_pagado_destino' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return redirect()->route('encomiendas.index')
                ->with('success', 'Encomienda registrada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log del error para debugging
            \Log::error('Error al registrar encomienda: ' . $e->getMessage(), [
                'data' => $validated ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Error al registrar la encomienda: ' . $e->getMessage());
        }
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

        return Inertia::render('Encomiendas/Show', [
            'encomienda' => $encomienda
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
        
        // Obtener viajes programados o en curso
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
            'img_url' => 'nullable|url|max:255',
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

            // Actualizar encomienda
            DB::table('encomiendas')
                ->where('venta_id', $id)
                ->update([
                    'ruta_id' => $validated['ruta_id'],
                    'viaje_id' => $validated['viaje_id'],
                    'peso' => $validated['peso'],
                    'descripcion' => $validated['descripcion'],
                    'nombre_destinatario' => $validated['nombre_destinatario'],
                    'img_url' => $validated['img_url'],
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

            // Actualizar monto pagado en destino sumando el monto pendiente
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

            // Eliminar encomienda (la venta se elimina por cascade)
            DB::table('encomiendas')->where('venta_id', $id)->delete();

            DB::commit();

            return redirect()->route('encomiendas.index')
                ->with('success', 'Encomienda eliminada exitosamente.');

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
