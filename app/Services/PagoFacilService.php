<?php

namespace App\Services;

use App\Models\PagoVenta;
use App\Models\Venta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PagoFacilService
{
    protected string $apiUrl;
    protected string $apiToken;
    protected string $clientCodePrefix;
    protected string $callbackUrl;
    protected string $queryUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.pagofacil.api_url');
        $this->apiToken = config('services.pagofacil.api_token');
        $this->clientCodePrefix = config('services.pagofacil.client_code_prefix', 'Grupo04SA');
        $this->callbackUrl = config('services.pagofacil.callback_url');
        $this->queryUrl = config('services.pagofacil.query_url');
    }

    /**
     * Generar QR de pago para un PagoVenta
     */
    public function generarQr(PagoVenta $pagoVenta): array
    {
        try {
            // Cargar relaciones necesarias
            $pagoVenta->load(['venta.usuario', 'venta.boletos.ruta', 'venta.encomienda.ruta']);
            
            $venta = $pagoVenta->venta;
            $cliente = $venta->usuario; // El cliente que realizó la compra

            // Construir payload
            $payload = [
                'paymentMethod' => 4, // Constante
                'clientName' => trim("{$cliente->nombre} {$cliente->apellido}"),
                'documentType' => 1, // Constante
                'documentId' => $cliente->ci,
                'phoneNumber' => $cliente->telefono ?? '',
                'email' => $cliente->correo ?? '',
                'paymentNumber' => "{$this->clientCodePrefix}_{$pagoVenta->id}",
                'amount' => (float) $pagoVenta->monto,
                'currency' => 2, // Constante
                'clientCode' => "{$this->clientCodePrefix}_{$cliente->id}",
                'callbackUrl' => $this->callbackUrl,
                'orderDetail' => $this->construirOrderDetail($venta),
            ];

            // Realizar petición a PagoFácil
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->apiToken}",
            ])->post($this->apiUrl, $payload);

            if (!$response->successful()) {
                throw new Exception("Error en API PagoFácil: " . $response->body());
            }

            $data = $response->json();

            // Validar respuesta
            if (isset($data['error']) && $data['error'] !== 0) {
                throw new Exception("Error de PagoFácil: " . ($data['message'] ?? 'Error desconocido'));
            }

            // Guardar datos en el pago
            if (isset($data['values'])) {
                $values = $data['values'];
                
                // El paymentNumber que enviamos (SIEMPRE usar este, no el que devuelve PagoFácil)
                $paymentNumber = $payload['paymentNumber']; // "Grupo04SA_{id}"
                
                $pagoVenta->update([
                    'qr_base64' => $values['qrBase64'] ?? null,
                    'transaction_id' => $values['transactionId'] ?? null,
                    // SIEMPRE guardar el paymentNumber que enviamos, no el que devuelve PagoFácil
                    // porque puede que PagoFácil devuelva un formato diferente
                    'payment_method_transaction_id' => $paymentNumber,
                ]);
            }

            return [
                'success' => true,
                'data' => $data,
                'pago_venta' => $pagoVenta->fresh(),
            ];

        } catch (Exception $e) {
            Log::error('Error generando QR PagoFácil', [
                'pago_venta_id' => $pagoVenta->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Consultar estado de una transacción
     */
    public function consultarEstado(string $transactionId = null, string $companyTransactionId = null): array
    {
        try {
            if (!$companyTransactionId && !$transactionId) {
                throw new Exception("Se requiere companyTransactionId o transactionId");
            }

            // Priorizar companyTransactionId (paymentNumber) si está disponible
            $payload = [];
            if ($companyTransactionId) {
                $payload['companyTransactionId'] = $companyTransactionId;
            } elseif ($transactionId) {
                $payload['pagofacilTransactionId'] = $transactionId;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->apiToken}",
            ])->post($this->queryUrl, $payload);

            if (!$response->successful()) {
                throw new Exception("Error en API PagoFácil: " . $response->body());
            }

            $data = $response->json();

            return [
                'success' => true,
                'data' => $data,
            ];

        } catch (Exception $e) {
            Log::error('Error consultando estado PagoFácil', [
                'transaction_id' => $transactionId,
                'company_transaction_id' => $companyTransactionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Consultar estado usando un PagoVenta
     */
    public function consultarEstadoPago(PagoVenta $pagoVenta): array
    {
        // Usar payment_method_transaction_id (que es el paymentNumber que enviamos)
        $companyTransactionId = $pagoVenta->payment_method_transaction_id;
        
        // Si no está guardado, construir el paymentNumber basado en el formato que usamos
        if (!$companyTransactionId) {
            $prefix = config('services.pagofacil.client_code_prefix', 'Grupo04SA');
            $companyTransactionId = "{$prefix}_{$pagoVenta->id}";
        }
        
        // Asegurarse de que tenga el formato correcto (con prefijo)
        // Si por alguna razón solo tiene números, reconstruirlo
        if (preg_match('/^\d+$/', $companyTransactionId)) {
            $prefix = config('services.pagofacil.client_code_prefix', 'Grupo04SA');
            $companyTransactionId = "{$prefix}_{$pagoVenta->id}";
        }
        
        return $this->consultarEstado(
            null, // No usar transaction_id, solo companyTransactionId
            $companyTransactionId
        );
    }

    /**
     * Construir array de orderDetail basado en el tipo de venta
     */
    protected function construirOrderDetail(Venta $venta): array
    {
        $orderDetail = [];
        $serial = 1;

        if ($venta->tipo === 'Boleto') {
            // Para boletos, crear un item por cada boleto
            foreach ($venta->boletos as $boleto) {
                $ruta = $boleto->ruta;
                $rutaNombre = $ruta->nombre ?? "{$ruta->origen} - {$ruta->destino}";
                
                $orderDetail[] = [
                    'serial' => $serial++,
                    'product' => "Boleto - Asiento {$boleto->asiento} - {$rutaNombre}",
                    'quantity' => 1,
                    'price' => (float) ($venta->monto_total / count($venta->boletos)),
                    'discount' => 0,
                    'total' => (float) ($venta->monto_total / count($venta->boletos)),
                ];
            }
        } elseif ($venta->tipo === 'Encomienda') {
            // Para encomiendas, crear un solo item
            $encomienda = $venta->encomienda;
            $ruta = $encomienda->ruta ?? null;
            $rutaNombre = $ruta ? ($ruta->nombre ?? "{$ruta->origen} - {$ruta->destino}") : 'Encomienda';
            
            $orderDetail[] = [
                'serial' => 1,
                'product' => "Encomienda - {$rutaNombre}" . ($encomienda->descripcion ? " - {$encomienda->descripcion}" : ''),
                'quantity' => 1,
                'price' => (float) $venta->monto_total,
                'discount' => 0,
                'total' => (float) $venta->monto_total,
            ];
        } else {
            // Fallback genérico
            $orderDetail[] = [
                'serial' => 1,
                'product' => "Venta #{$venta->id} - {$venta->tipo}",
                'quantity' => 1,
                'price' => (float) $venta->monto_total,
                'discount' => 0,
                'total' => (float) $venta->monto_total,
            ];
        }

        return $orderDetail;
    }
}

