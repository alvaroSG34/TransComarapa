<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ClienteHistorialController extends Controller
{
    /**
     * Mostrar el historial de compras del cliente autenticado
     */
    public function index(Request $request)
    {
        $cliente = $request->user();
        
        if (!$cliente) {
            return redirect()->route('login');
        }

        // Obtener todas las ventas del cliente con información detallada
        $query = DB::table('ventas')
            ->where('ventas.usuario_id', $cliente->id)
            ->leftJoin('boletos', 'ventas.id', '=', 'boletos.venta_id')
            ->leftJoin('encomiendas', 'ventas.id', '=', 'encomiendas.venta_id')
            ->leftJoin('viajes', 'boletos.viaje_id', '=', 'viajes.id')
            ->leftJoin('rutas as ruta_boleto', 'viajes.ruta_id', '=', 'ruta_boleto.id')
            ->leftJoin('rutas as ruta_encomienda', 'encomiendas.ruta_id', '=', 'ruta_encomienda.id')
            ->leftJoin('vehiculos', 'ventas.vehiculo_id', '=', 'vehiculos.id')
            ->leftJoin('pago_ventas', function($join) {
                $join->on('ventas.id', '=', 'pago_ventas.venta_id')
                     ->where('pago_ventas.num_cuota', '=', 1);
            })
            ->select(
                'ventas.id as venta_id',
                'ventas.fecha',
                'ventas.monto_total',
                'ventas.tipo',
                'ventas.estado_pago',
                'ventas.created_at',
                // Datos del boleto
                'boletos.id as boleto_id',
                'boletos.asiento',
                'viajes.fecha_salida',
                'viajes.fecha_llegada',
                'viajes.estado as viaje_estado',
                'ruta_boleto.nombre as ruta_boleto_nombre',
                'ruta_boleto.origen as ruta_boleto_origen',
                'ruta_boleto.destino as ruta_boleto_destino',
                'vehiculos.placa as vehiculo_placa',
                'vehiculos.marca as vehiculo_marca',
                'vehiculos.modelo as vehiculo_modelo',
                // Datos de la encomienda
                'encomiendas.venta_id as encomienda_id',
                'encomiendas.peso',
                'encomiendas.nombre_destinatario',
                'encomiendas.modalidad_pago',
                'ruta_encomienda.nombre as ruta_encomienda_nombre',
                'ruta_encomienda.origen as ruta_encomienda_origen',
                'ruta_encomienda.destino as ruta_encomienda_destino',
                // Datos de pago
                'pago_ventas.metodo_pago',
                'pago_ventas.qr_base64',
                'pago_ventas.transaction_id',
                'pago_ventas.estado_pago as pago_estado'
            )
            ->orderBy('ventas.fecha', 'desc');

        // Filtros opcionales
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

        $compras = $query->get();

        // Agrupar por venta para evitar duplicados
        $comprasAgrupadas = $compras->groupBy('venta_id')->map(function ($items) {
            $venta = $items->first();
            
            // Obtener todos los boletos de esta venta
            $boletos = $items->filter(function ($item) {
                return $item->boleto_id !== null;
            })->map(function ($item) {
                return [
                    'id' => $item->boleto_id,
                    'asiento' => $item->asiento,
                    'fecha_salida' => $item->fecha_salida,
                    'fecha_llegada' => $item->fecha_llegada,
                    'viaje_estado' => $item->viaje_estado,
                    'ruta_nombre' => $item->ruta_boleto_nombre,
                    'origen' => $item->ruta_boleto_origen,
                    'destino' => $item->ruta_boleto_destino,
                ];
            })->values();

            // Obtener encomienda si existe
            $encomienda = $items->first(function ($item) {
                return $item->encomienda_id !== null;
            });

            return [
                'id' => $venta->venta_id,
                'fecha' => $venta->fecha,
                'monto_total' => $venta->monto_total,
                'tipo' => $venta->tipo,
                'estado_pago' => $venta->estado_pago,
                'created_at' => $venta->created_at,
                'boletos' => $boletos,
                'encomienda' => $encomienda ? [
                    'id' => $encomienda->encomienda_id,
                    'peso' => $encomienda->peso,
                    'nombre_destinatario' => $encomienda->nombre_destinatario,
                    'modalidad_pago' => $encomienda->modalidad_pago,
                    'ruta_nombre' => $encomienda->ruta_encomienda_nombre,
                    'origen' => $encomienda->ruta_encomienda_origen,
                    'destino' => $encomienda->ruta_encomienda_destino,
                ] : null,
                'pago' => [
                    'metodo_pago' => $venta->metodo_pago,
                    'qr_base64' => $venta->qr_base64,
                    'transaction_id' => $venta->transaction_id,
                    'estado_pago' => $venta->pago_estado,
                ],
                'vehiculo' => [
                    'placa' => $venta->vehiculo_placa,
                    'marca' => $venta->vehiculo_marca,
                    'modelo' => $venta->vehiculo_modelo,
                ],
            ];
        })->values();

        return Inertia::render('Cliente/Historial', [
            'compras' => $comprasAgrupadas,
            'filtros' => $request->only(['tipo', 'estado_pago', 'fecha_desde', 'fecha_hasta'])
        ]);
    }
}

