<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Solo clientes, no admins ni secretarias
        $query = Usuario::where('rol', 'Cliente');

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                  ->orWhere('apellido', 'LIKE', "%{$search}%")
                  ->orWhere('ci', 'LIKE', "%{$search}%");
            });
        }
        
        // Incluir clientes baneados (eliminados soft) si se solicita, o mostrarlos siempre marcados
        $query->withTrashed();

        $clientes = $query->orderBy('created_at', 'desc')->paginate(10);

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cliente = Usuario::withTrashed()->findOrFail($id);

        // Obtener historial de compras (Ventas)
        // Podemos reutilizar la lógica de VentaRepository o hacer query directa aquí para simplificar
        // dado que es una vista específica de "Perfil de cliente"
        
        $historial = DB::table('ventas')
            ->where('usuario_id', $id)
            ->leftJoin('boletos', 'ventas.id', '=', 'boletos.venta_id')
            ->leftJoin('encomiendas', 'ventas.id', '=', 'encomiendas.venta_id')
            ->leftJoin('viajes', 'boletos.viaje_id', '=', 'viajes.id')
            ->leftJoin('rutas as ruta_boleto', 'viajes.ruta_id', '=', 'ruta_boleto.id')
            ->leftJoin('rutas as ruta_encomienda', 'encomiendas.ruta_id', '=', 'ruta_encomienda.id')
            ->select(
                'ventas.id',
                'ventas.fecha',
                'ventas.monto_total',
                'ventas.tipo',
                'ventas.estado_pago',
                // Detalles
                'ruta_boleto.nombre as ruta_boleto',
                'ruta_encomienda.nombre as ruta_encomienda',
                'boletos.asiento',
                'encomiendas.nombre_destinatario'
            )
            ->orderBy('ventas.fecha', 'desc')
            ->get();

        return Inertia::render('Clientes/Show', [
            'cliente' => $cliente,
            'historial' => $historial
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cliente = Usuario::findOrFail($id);
        $cliente->delete(); // Soft delete

        return back()->with('success', 'Cliente baneado exitosamente.');
    }
    
    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        $cliente = Usuario::withTrashed()->findOrFail($id);
        $cliente->restore();

        return back()->with('success', 'Cliente reactivado exitosamente.');
    }
}

