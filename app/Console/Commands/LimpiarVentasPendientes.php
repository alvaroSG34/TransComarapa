<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\StripeService;

class LimpiarVentasPendientes extends Command
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        parent::__construct();
        $this->stripeService = $stripeService;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ventas:limpiar-pendientes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina ventas pendientes de Stripe con más de 30 minutos de antigüedad para liberar asientos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando limpieza de ventas pendientes...');

        // Buscar ventas pendientes de Stripe con más de 30 minutos
        $ventasPendientes = DB::table('ventas')
            ->join('boletos', 'ventas.id', '=', 'boletos.venta_id')
            ->join('pago_ventas', 'ventas.id', '=', 'pago_ventas.venta_id')
            ->where('ventas.estado_pago', 'Pendiente')
            ->where('pago_ventas.metodo_pago', 'Stripe')
            ->where('ventas.created_at', '<=', now()->subMinutes(30))
            ->select(
                'ventas.id', 
                'boletos.id as boleto_id', 
                'pago_ventas.id as pago_id', 
                'pago_ventas.payment_intent_id',
                'ventas.usuario_id', 
                'boletos.asiento'
            )
            ->get();

        $count = 0;
        $skipped = 0;

        foreach ($ventasPendientes as $venta) {
            try {
                // Verificar el estado real en Stripe antes de eliminar
                if ($venta->payment_intent_id) {
                    $estadoStripe = $this->stripeService->consultarEstado($venta->payment_intent_id);
                    
                    // Si el pago está exitoso en Stripe, NO eliminar y actualizar estado
                    if ($estadoStripe['success'] && in_array($estadoStripe['status'], ['succeeded', 'processing'])) {
                        DB::table('pago_ventas')
                            ->where('id', $venta->pago_id)
                            ->update(['estado_pago' => 'Pagado']);
                        
                        DB::table('ventas')
                            ->where('id', $venta->id)
                            ->update(['estado_pago' => 'Pagado']);
                        
                        Log::warning('Venta pendiente actualizada a Pagado (webhook retrasado)', [
                            'venta_id' => $venta->id,
                            'payment_intent_id' => $venta->payment_intent_id,
                            'stripe_status' => $estadoStripe['status'],
                        ]);
                        
                        $skipped++;
                        continue; // No eliminar, solo actualizar
                    }
                }
                
                // Si no está pagado en Stripe, eliminar
                DB::table('pago_ventas')->where('id', $venta->pago_id)->delete();
                DB::table('boletos')->where('id', $venta->boleto_id)->delete();
                DB::table('ventas')->where('id', $venta->id)->delete();

                $count++;

                Log::info("Venta pendiente eliminada", [
                    'venta_id' => $venta->id,
                    'usuario_id' => $venta->usuario_id,
                    'asiento' => $venta->asiento,
                ]);
            } catch (\Exception $e) {
                Log::error("Error al eliminar venta pendiente: " . $e->getMessage(), [
                    'venta_id' => $venta->id,
                ]);
            }
        }

        $this->info("✅ Limpieza completada. {$count} ventas eliminadas, {$skipped} ventas recuperadas (pagadas pero webhook retrasado).");

        return Command::SUCCESS;
    }
}
