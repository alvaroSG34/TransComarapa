<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\ViajeRepositoryInterface;
use App\Repositories\Contracts\RutaRepositoryInterface;
use App\Repositories\Contracts\VehiculoRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ViajeController extends Controller
{
    protected $viajeRepository;
    protected $rutaRepository;
    protected $vehiculoRepository;

    public function __construct(
        ViajeRepositoryInterface $viajeRepository,
        RutaRepositoryInterface $rutaRepository,
        VehiculoRepositoryInterface $vehiculoRepository
    ) {
        $this->viajeRepository = $viajeRepository;
        $this->rutaRepository = $rutaRepository;
        $this->vehiculoRepository = $vehiculoRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $viajes = $this->viajeRepository->all();
        
        return Inertia::render('Viajes/Index', [
            'viajes' => $viajes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rutas = $this->rutaRepository->all();
        $vehiculos = $this->vehiculoRepository->all();

        return Inertia::render('Viajes/Create', [
            'rutas' => $rutas,
            'vehiculos' => $vehiculos
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ruta_id' => 'required|exists:rutas,id',
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'fecha_salida' => 'required|date|after:now',
            'fecha_llegada' => 'nullable|date|after:fecha_salida',
            'precio' => 'required|numeric|min:0',
            'asientos_totales' => 'required|integer|min:1|max:100',
        ]);

        // Obtener la ruta para heredar su moneda
        $ruta = $this->rutaRepository->find($validated['ruta_id']);
        $validated['moneda'] = $ruta->moneda ?? 'BOB';

        $this->viajeRepository->create($validated);

        return redirect()->route('viajes.index')
            ->with('success', 'Viaje creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $viaje = $this->viajeRepository->find($id);

        if (!$viaje) {
            return redirect()->route('viajes.index')
                ->with('error', 'Viaje no encontrado.');
        }

        return Inertia::render('Viajes/Show', [
            'viaje' => $viaje
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $viaje = $this->viajeRepository->find($id);
        $rutas = $this->rutaRepository->all();
        $vehiculos = $this->vehiculoRepository->all();

        if (!$viaje) {
            return redirect()->route('viajes.index')
                ->with('error', 'Viaje no encontrado.');
        }

        return Inertia::render('Viajes/Edit', [
            'viaje' => $viaje,
            'rutas' => $rutas,
            'vehiculos' => $vehiculos
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'ruta_id' => 'required|exists:rutas,id',
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'fecha_salida' => 'required|date',
            'fecha_llegada' => 'nullable|date|after:fecha_salida',
            'precio' => 'required|numeric|min:0',
            'asientos_totales' => 'required|integer|min:1|max:100',
        ]);

        // Heredar moneda de la ruta si cambió la ruta
        $ruta = $this->rutaRepository->find($validated['ruta_id']);
        $validated['moneda'] = $ruta->moneda ?? 'BOB';

        $updated = $this->viajeRepository->update($id, $validated);

        if (!$updated) {
            return redirect()->route('viajes.index')
                ->with('error', 'Error al actualizar el viaje.');
        }

        return redirect()->route('viajes.index')
            ->with('success', 'Viaje actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deleted = $this->viajeRepository->delete($id);

        if (!$deleted) {
            return redirect()->route('viajes.index')
                ->with('error', 'Error al eliminar el viaje.');
        }

        return redirect()->route('viajes.index')
            ->with('success', 'Viaje eliminado exitosamente.');
    }

    /**
     * Cambiar estado del viaje
     */
    public function cambiarEstado(Request $request, string $id)
    {
        $validated = $request->validate([
            'estado' => 'required|in:programado,en_curso,finalizado,cancelado'
        ]);

        $viaje = $this->viajeRepository->cambiarEstado($id, $validated['estado']);

        if (!$viaje) {
            return back()->with('error', 'Error al cambiar el estado del viaje.');
        }

        return back()->with('success', 'Estado del viaje actualizado exitosamente.');
    }
}
