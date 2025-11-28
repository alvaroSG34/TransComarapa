<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PagoVenta;
use App\Events\PagoVentaUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PagoFacilCallbackController extends Controller
{
    /**
     * Recibir callback de PagoFácil cuando cambia el estado de un pago
     * 
     * Estados de PagoFácil:
     * 1 - Pendiente
     * 2 - Completado/Pagado
     * 3 - Anulado
     * 4 - Vencido
     * 5 - Validación Pendiente
     */
    public function callback(Request $request)
    {
        try {
            // Log de la petición recibida para debugging
            Log::info('PagoFácil Callback recibido', [
                'payload' => $request->all(),
                'headers' => $request->headers->all(),
            ]);

            // Obtener datos del callback
            $companyTransactionId = $request->input('CompanyTransactionId'); // "Grupo04SA_123"
            $transactionId = $request->input('PagofacilTransactionId'); // ID de PagoFácil
            $estado = $request->input('State'); // 1, 2, 3, 4, 5
            $message = $request->input('Message', '');

            // Validar que tengamos los datos necesarios
            if (!$companyTransactionId && !$transactionId) {
                Log::warning('Callback sin CompanyTransactionId ni PagofacilTransactionId');
                return response()->json([
                    'error' => 'Datos incompletos'
                ], 400);
            }

            // Buscar el PagoVenta
            $pagoVenta = null;

            // Primero intentar buscar por payment_method_transaction_id (CompanyTransactionId)
            if ($companyTransactionId) {
                $pagoVenta = PagoVenta::where('payment_method_transaction_id', $companyTransactionId)->first();
                
                // Si no se encuentra, extraer el ID del formato "Grupo04SA_123"
                if (!$pagoVenta && str_contains($companyTransactionId, '_')) {
                    $parts = explode('_', $companyTransactionId);
                    $pagoId = end($parts);
                    if (is_numeric($pagoId)) {
                        $pagoVenta = PagoVenta::find($pagoId);
                    }
                }
            }

            // Si aún no se encuentra, buscar por transaction_id
            if (!$pagoVenta && $transactionId) {
                $pagoVenta = PagoVenta::where('transaction_id', $transactionId)->first();
            }

            if (!$pagoVenta) {
                Log::warning('PagoVenta no encontrado', [
                    'company_transaction_id' => $companyTransactionId,
                    'transaction_id' => $transactionId,
                ]);
                
                return response()->json([
                    'error' => 'Pago no encontrado'
                ], 404);
            }

            // Actualizar el estado del pago según el estado de PagoFácil
            $estadoAnterior = $pagoVenta->estado_pago;
            $nuevoEstado = $this->mapearEstadoPagoFacil($estado);

            // Solo actualizar si el estado cambió
            if ($estadoAnterior !== $nuevoEstado) {
                $pagoVenta->update([
                    'estado_pago' => $nuevoEstado,
                    'transaction_id' => $transactionId ?? $pagoVenta->transaction_id,
                ]);

                Log::info('Estado de PagoVenta actualizado', [
                    'pago_venta_id' => $pagoVenta->id,
                    'estado_anterior' => $estadoAnterior,
                    'nuevo_estado' => $nuevoEstado,
                    'estado_pagofacil' => $estado,
                    'message' => $message,
                ]);

                // Disparar evento para actualizar el estado de la venta
                event(new PagoVentaUpdated($pagoVenta));
            }

            // Responder a PagoFácil con éxito
            return response()->json([
                'success' => true,
                'message' => 'Callback procesado correctamente',
                'pago_venta_id' => $pagoVenta->id,
                'estado_actualizado' => $nuevoEstado,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error procesando callback de PagoFácil', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error interno procesando callback',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mapear estado de PagoFácil a nuestro sistema
     */
    protected function mapearEstadoPagoFacil($estado): string
    {
        return match ((int) $estado) {
            1 => 'Pendiente',           // Pendiente
            2 => 'Pagado',              // Completado/Pagado
            3 => 'Cancelado',           // Anulado
            4 => 'Cancelado',           // Vencido
            5 => 'Pagado',              // Validación Pendiente (también se considera pagado)
            default => 'Pendiente',
        };
    }
}

