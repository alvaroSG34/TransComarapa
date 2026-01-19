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

    // Tasas de conversión aproximadas a USD (actualizadas a enero 2026)
    protected $conversionRates = [
        'USD' => 1.000000,

        'BOB' => 0.144303,
        'ARS' => 0.000689,
        'AUD' => 0.669758,
        'BRL' => 0.185644,
        'CAD' => 0.721369,
        'CLP' => 0.001116,
        'CNY' => 0.142936,
        'COP' => 0.000268,
        'CRC' => 0.002011,
        'DKK' => 0.156313,
        'EUR' => 1.166310,
        'GBP' => 1.343743,
        'GTQ' => 0.130443,
        'HNL' => 0.037910,
        'INR' => 0.011113,
        'JPY' => 0.006376,
        'KRW' => 0.000689,
        'MXN' => 0.055631,
        'NIO' => 0.027168,
        'NOK' => 0.099129,
        'PEN' => 0.297340,
        'PYG' => 0.000150,
        'RON' => 0.229401,
        'RUB' => 0.012469,
        'SEK' => 0.108447,
        'CHF' => 1.252263,
        'UYU' => 0.025659,
        'DOP' => 0.015755,
    ];


    public function __construct()
    {
        $this->stripeSecret = config('services.stripe.secret');
        $this->stripeCurrency = config('services.stripe.currency', 'USD');
        $this->bobToUsdRate = config('services.stripe.bob_to_usd_rate', 0.145);

        Stripe::setApiKey($this->stripeSecret);
    }

    /**
     * Convertir cualquier moneda a USD
     */
    public function convertirAUsd(float $monto, string $moneda): float
    {
        if ($moneda === 'USD') {
            return $monto;
        }

        $tasa = $this->conversionRates[$moneda] ?? 1.0;
        return round($monto * $tasa, 2);
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
     * @param string $moneda Código ISO de moneda del usuario (BOB, USD, PEN, etc.)
     * @return array ['success' => bool, 'payment_intent' => PaymentIntent|null, 'error' => string|null]
     */
    public function crearPaymentIntent(PagoVenta $pagoVenta, string $moneda = 'BOB'): array
    {
        try {
            // Obtener el viaje/venta para saber en qué moneda está el precio original
            $venta = $pagoVenta->venta;
            $viaje = $venta->boletos->first()?->viaje; // Si es boleto
            $monedaViaje = $viaje->moneda ?? 'BOB'; // Moneda original del viaje
            $montoPrecio = $pagoVenta->monto;

            // Convertir desde moneda del viaje a USD para Stripe
            $montoUsd = $this->convertirAUsd($montoPrecio, $monedaViaje);

            // Validar monto mínimo de Stripe ($0.50 USD)
            if ($montoUsd < 0.50) {
                Log::warning('Monto inferior al mínimo de Stripe', [
                    'pago_venta_id' => $pagoVenta->id,
                    'moneda_viaje' => $monedaViaje,
                    'monto_original' => $montoPrecio,
                    'monto_usd' => $montoUsd,
                    'minimo_requerido_usd' => 0.50
                ]);

                return [
                    'success' => false,
                    'payment_intent' => null,
                    'client_secret' => null,
                    'error' => "El monto es demasiado bajo para procesarlo con tarjeta. Mínimo requerido: $0.50 USD (equivalente a aproximadamente {$this->calcularMontoMinimo($monedaViaje)} {$monedaViaje}). Por favor, use el método de pago QR.",
                ];
            }

            $montoStripe = (int) ($montoUsd * 100); // Stripe usa centavos

            // Guardar información de la transacción
            $pagoVenta->update([
                'moneda' => $moneda, // Moneda del usuario
                'monto_usd' => $montoUsd
            ]);

            // Crear PaymentIntent
            $paymentIntent = PaymentIntent::create([
                'amount' => $montoStripe,
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'metadata' => [
                    'pago_venta_id' => $pagoVenta->id,
                    'venta_id' => $pagoVenta->venta_id,
                    'moneda_usuario' => $moneda,
                    'moneda_viaje' => $monedaViaje,
                    'monto_original' => $montoPrecio,
                    'monto_usd' => $montoUsd,
                ],
                'description' => "Pago venta #{$pagoVenta->venta_id} - TransPorta",
            ]);

            // Guardar payment_intent_id en la base de datos
            $pagoVenta->update([
                'payment_intent_id' => $paymentIntent->id,
            ]);

            Log::info('PaymentIntent creado exitosamente', [
                'payment_intent_id' => $paymentIntent->id,
                'pago_venta_id' => $pagoVenta->id,
                'moneda_usuario' => $moneda,
                'moneda_viaje' => $monedaViaje,
                'monto_original' => $montoPrecio,
                'monto_usd' => $montoUsd,
                'amount_stripe' => $montoStripe,
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
     * Calcular monto mínimo en moneda local para cumplir con $0.50 USD de Stripe
     */
    protected function calcularMontoMinimo(string $moneda): string
    {
        $tasa = $this->conversionRates[$moneda] ?? 1.0;
        $montoMinimo = 0.50 / $tasa;

        // Redondear según la moneda
        $sinDecimales = ['JPY', 'KRW', 'CLP', 'PYG', 'COP'];
        if (in_array($moneda, $sinDecimales)) {
            return number_format(ceil($montoMinimo), 0, '', '');
        }
        return number_format($montoMinimo, 2);
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
