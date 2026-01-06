<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Manejar webhooks de Stripe
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        // Verificar y construir evento
        $resultado = $this->stripeService->procesarWebhook($payload, $signature);

        if (!$resultado['success']) {
            Log::error('Error al procesar webhook de Stripe', [
                'error' => $resultado['error'],
            ]);
            return response()->json(['error' => $resultado['error']], 400);
        }

        $event = $resultado['event'];

        // Manejar diferentes tipos de eventos
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->stripeService->manejarPagoExitoso($event);
                Log::info('Evento payment_intent.succeeded procesado', [
                    'event_id' => $event->id,
                ]);
                break;

            case 'payment_intent.payment_failed':
                $this->stripeService->manejarPagoFallido($event);
                Log::info('Evento payment_intent.payment_failed procesado', [
                    'event_id' => $event->id,
                ]);
                break;

            case 'payment_intent.created':
                Log::info('PaymentIntent creado', [
                    'event_id' => $event->id,
                ]);
                break;

            default:
                Log::info('Evento de Stripe no manejado', [
                    'event_type' => $event->type,
                    'event_id' => $event->id,
                ]);
        }

        return response()->json(['status' => 'success'], 200);
    }
}
