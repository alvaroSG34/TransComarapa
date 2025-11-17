<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\VentaRepositoryInterface;
use App\Services\VentaService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VentaController extends Controller
{
    protected $ventaRepository;
    protected $ventaService;

    public function __construct(
        VentaRepositoryInterface $ventaRepository,
        VentaService $ventaService
    ) {
        $this->ventaRepository = $ventaRepository;
        $this->ventaService = $ventaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ventas = $this->ventaRepository->all();

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Ventas/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:boleto,encomienda',
            'fecha' => 'required|date',
            'monto_total' => 'required|numeric|min:0',
            'usuario_id' => 'required|exists:usuarios,id',
            'vehiculo_id' => 'required|exists:vehiculos,id',
        ]);

        if ($validated['tipo'] === 'boleto') {
            $venta = $this->ventaService->crearVentaBoleto($validated);
        } else {
            $venta = $this->ventaService->crearVentaEncomienda($validated);
        }

        return redirect()->route('ventas.show', $venta->id)
            ->with('success', 'Venta creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $venta = $this->ventaRepository->findWithRelations($id);

        if (!$venta) {
            abort(404);
        }

        return Inertia::render('Ventas/Show', [
            'venta' => $venta
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $venta = $this->ventaRepository->find($id);

        if (!$venta) {
            abort(404);
        }

        return Inertia::render('Ventas/Edit', [
            'venta' => $venta
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'estado_pago' => 'sometimes|in:pendiente,pagado,anulado',
        ]);

        $venta = $this->ventaRepository->update($id, $validated);

        if (!$venta) {
            abort(404);
        }

        return redirect()->route('ventas.show', $id)
            ->with('success', 'Venta actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $result = $this->ventaService->cancelarVenta($id);

        if (!$result) {
            abort(404);
        }

        return redirect()->route('ventas.index')
            ->with('success', 'Venta cancelada exitosamente');
    }

    /**
     * Get ventas pendientes
     */
    public function pendientes()
    {
        $ventas = $this->ventaRepository->findPendientes();

        return Inertia::render('Ventas/Pendientes', [
            'ventas' => $ventas
        ]);
    }
}
