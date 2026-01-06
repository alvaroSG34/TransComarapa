<?php

namespace App\Services;

use App\Models\PagoVenta;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class StripeService
{
    protected $stripeSecret;
    protected $stripeCurrency;
    protected $bobToUsdRate;

    public function __construct()
    {
        $this->stripeSecret = config('services.stripe.secret');
        $this->stripeCurrency = config('services.stripe.currency', 'USD');
        $this->bobToUsdRate = config('services.stripe.bob_to_usd_rate', 0.145);
        
        Stripe::setApiKey($this->stripeSecret);
    }

    /**
     * Convertir BOB a USD
     */
    public function convertirBobAUsd(float $montoBob): float
    {
        return round($montoBob * $this->bobToUsdRate, 2);
    }

    /**
     * Convertir USD a BOB
     */
    public function convertirUsdABob(float $montoUsd): float
    {
        return round($montoUsd / $this->bobToUsdRate, 2);
    }

    /**
     * Crear PaymentIntent en Stripe
     * 
     * @param PagoVenta $pagoVenta
     * @param string $moneda ('BOB' o 'USD')
     * @return array ['success' => bool, 'payment_intent' => PaymentIntent|null, 'error' => string|null]
     */
    public function crearPaymentIntent(PagoVenta $pagoVenta, string $moneda = 'BOB'): array
    {
        try {
            // Obtener monto según moneda
            $monto = $pagoVenta->monto;
            $montoStripe = null;

            if ($moneda === 'BOB') {
                // Convertir BOB a USD para Stripe
                $montoUsd = $this->convertirBobAUsd($monto);
                $montoStripe = (int)($montoUsd * 100); // Stripe usa centavos
                $pagoVenta->update([
                    'moneda' => 'BOB',
                    'monto_usd' => $montoUsd
                ]);
            } else {
                // Ya está en USD
                $montoStripe = (int)($monto * 100);
                $pagoVenta->update([
                    'moneda' => 'USD',
                    'monto_usd' => $monto
                ]);
            }

            // Crear PaymentIntent
            $paymentIntent = PaymentIntent::create([
                'amount' => $montoStripe,
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'metadata' => [
                    'pago_venta_id' => $pagoVenta->id,
                    'venta_id' => $pagoVenta->venta_id,
                    'moneda_original' => $moneda,
                    'monto_original' => $monto,
                ],
                'description' => "Pago venta #{$pagoVenta->venta_id} - TransComarapa",
            ]);

            // Guardar payment_intent_id en la base de datos
            $pagoVenta->update([
                'payment_intent_id' => $paymentIntent->id,
            ]);

            Log::info('PaymentIntent creado exitosamente', [
                'payment_intent_id' => $paymentIntent->id,
                'pago_venta_id' => $pagoVenta->id,
                'amount' => $montoStripe,
                'currency' => 'usd',
            ]);

            return [
                'success' => true,
                'payment_intent' => $paymentIntent,
                'client_secret' => $paymentIntent->client_secret,
                'error' => null,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Error al crear PaymentIntent en Stripe', [
                'pago_venta_id' => $pagoVenta->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'payment_intent' => null,
                'client_secret' => null,
                'error' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::error('Error inesperado al crear PaymentIntent', [
                'pago_venta_id' => $pagoVenta->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'payment_intent' => null,
                'client_secret' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Consultar estado de un PaymentIntent
     * 
     * @param string $paymentIntentId
     * @return array ['success' => bool, 'status' => string|null, 'payment_intent' => PaymentIntent|null]
     */
    public function consultarEstado(string $paymentIntentId): array
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            return [
                'success' => true,
                'status' => $paymentIntent->status,
                'payment_intent' => $paymentIntent,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Error al consultar estado de PaymentIntent', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => null,
                'payment_intent' => null,
            ];
        }
    }

    /**
     * Procesar webhook de Stripe
     * 
     * @param string $payload
     * @param string $signature
     * @return array ['success' => bool, 'event' => \Stripe\Event|null, 'error' => string|null]
     */
    public function procesarWebhook(string $payload, string $signature): array
    {
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $webhookSecret
            );

            Log::info('Webhook de Stripe recibido', [
                'event_type' => $event->type,
                'event_id' => $event->id,
            ]);

            return [
                'success' => true,
                'event' => $event,
                'error' => null,
            ];
        } catch (\UnexpectedValueException $e) {
            Log::error('Payload de webhook inválido', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'event' => null,
                'error' => 'Invalid payload',
            ];
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Firma de webhook inválida', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'event' => null,
                'error' => 'Invalid signature',
            ];
        }
    }

    /**
     * Manejar evento payment_intent.succeeded
     * 
     * @param \Stripe\Event $event
     * @return bool
     */
    public function manejarPagoExitoso(\Stripe\Event $event): bool
    {
        try {
            $paymentIntent = $event->data->object;

            // Buscar PagoVenta por payment_intent_id
            $pagoVenta = PagoVenta::where('payment_intent_id', $paymentIntent->id)->first();

            if (!$pagoVenta) {
                Log::warning('PagoVenta no encontrado para PaymentIntent', [
                    'payment_intent_id' => $paymentIntent->id,
                ]);
                return false;
            }

            // Actualizar estado del pago
            $pagoVenta->update([
                'estado_pago' => 'Pagado',
                'payment_method_id' => $paymentIntent->payment_method ?? null,
            ]);

            Log::info('Pago Stripe procesado exitosamente', [
                'pago_venta_id' => $pagoVenta->id,
                'payment_intent_id' => $paymentIntent->id,
            ]);

            // El evento PagoVentaUpdated se disparará automáticamente
            // y actualizará el estado_pago de la Venta

            return true;
        } catch (\Exception $e) {
            Log::error('Error al manejar pago exitoso de Stripe', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntent->id ?? null,
            ]);
            return false;
        }
    }

    /**
     * Manejar evento payment_intent.payment_failed
     * 
     * @param \Stripe\Event $event
     * @return bool
     */
    public function manejarPagoFallido(\Stripe\Event $event): bool
    {
        try {
            $paymentIntent = $event->data->object;

            $pagoVenta = PagoVenta::where('payment_intent_id', $paymentIntent->id)->first();

            if (!$pagoVenta) {
                Log::warning('PagoVenta no encontrado para PaymentIntent fallido', [
                    'payment_intent_id' => $paymentIntent->id,
                ]);
                return false;
            }

            Log::warning('Pago Stripe fallido', [
                'pago_venta_id' => $pagoVenta->id,
                'payment_intent_id' => $paymentIntent->id,
                'error' => $paymentIntent->last_payment_error->message ?? 'Unknown error',
            ]);

            // Opcionalmente actualizar estado a "Fallido"
            // $pagoVenta->update(['estado_pago' => 'Fallido']);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al manejar pago fallido de Stripe', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
