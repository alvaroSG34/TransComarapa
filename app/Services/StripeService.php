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
        'USD' => 1.0,
        'BOB' => 0.145,      // 1 BOB = 0.145 USD
        'ARS' => 0.001,      // 1 ARS = 0.001 USD
        'AUD' => 0.67,       // 1 AUD = 0.67 USD
        'BRL' => 0.20,       // 1 BRL = 0.20 USD
        'CAD' => 0.74,       // 1 CAD = 0.74 USD
        'CLP' => 0.0011,     // 1 CLP = 0.0011 USD
        'CNY' => 0.14,       // 1 CNY = 0.14 USD
        'COP' => 0.00025,    // 1 COP = 0.00025 USD
        'CRC' => 0.0019,     // 1 CRC = 0.0019 USD
        'DKK' => 0.14,       // 1 DKK = 0.14 USD
        'EUR' => 1.10,       // 1 EUR = 1.10 USD
        'GBP' => 1.27,       // 1 GBP = 1.27 USD
        'GTQ' => 0.13,       // 1 GTQ = 0.13 USD
        'HNL' => 0.040,      // 1 HNL = 0.040 USD
        'INR' => 0.012,      // 1 INR = 0.012 USD
        'JPY' => 0.0069,     // 1 JPY = 0.0069 USD
        'KRW' => 0.00075,    // 1 KRW = 0.00075 USD
        'MXN' => 0.059,      // 1 MXN = 0.059 USD
        'NIO' => 0.027,      // 1 NIO = 0.027 USD
        'NOK' => 0.094,      // 1 NOK = 0.094 USD
        'PEN' => 0.27,       // 1 PEN = 0.27 USD
        'PYG' => 0.00013,    // 1 PYG = 0.00013 USD
        'RON' => 0.22,       // 1 RON = 0.22 USD
        'RUB' => 0.010,      // 1 RUB = 0.010 USD
        'SEK' => 0.096,      // 1 SEK = 0.096 USD
        'CHF' => 1.17,       // 1 CHF = 1.17 USD
        'UYU' => 0.025,      // 1 UYU = 0.025 USD
        'DOP' => 0.017,      // 1 DOP = 0.017 USD
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
            
            $montoStripe = (int)($montoUsd * 100); // Stripe usa centavos

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
                'description' => "Pago venta #{$pagoVenta->venta_id} - TransComarapa",
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
