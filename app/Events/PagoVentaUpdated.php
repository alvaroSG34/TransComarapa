<?php

namespace App\Events;

use App\Models\PagoVenta;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PagoVentaUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PagoVenta $pagoVenta
    ) {}
}
