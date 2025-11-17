<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\VehiculoRepositoryInterface;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehiculoController extends Controller
{
    protected $vehiculoRepository;
    protected $usuarioRepository;

    public function __construct(
        VehiculoRepositoryInterface $vehiculoRepository,
        UsuarioRepositoryInterface $usuarioRepository
    )
    {
        $this->vehiculoRepository = $vehiculoRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehiculos = $this->vehiculoRepository->all();
        
        return Inertia::render('Vehiculos/Index', [
            'vehiculos' => $vehiculos
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $conductores = $this->usuarioRepository->findByRol('Conductor');
        
        return Inertia::render('Vehiculos/Create', [
            'conductores' => $conductores
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'placa' => 'required|string|max:20|unique:vehiculos,placa',
            'marca' => 'required|string|max:50',
            'modelo' => 'required|string|max:50',
            'conductor_id' => 'nullable|exists:usuarios,id',
            'img_url' => 'nullable|string|max:255',
        ]);

        $this->vehiculoRepository->create($validated);

        return redirect()->route('vehiculos.index')
            ->with('success', 'Vehículo registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $vehiculo = $this->vehiculoRepository->find($id);

        if (!$vehiculo) {
            return redirect()->route('vehiculos.index')
                ->with('error', 'Vehículo no encontrado.');
        }

        return Inertia::render('Vehiculos/Show', [
            'vehiculo' => $vehiculo
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $vehiculo = $this->vehiculoRepository->find($id);

        if (!$vehiculo) {
            return redirect()->route('vehiculos.index')
                ->with('error', 'Vehículo no encontrado.');
        }

        $conductores = $this->usuarioRepository->findByRol('Conductor');

        return Inertia::render('Vehiculos/Edit', [
            'vehiculo' => $vehiculo,
            'conductores' => $conductores
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'placa' => 'required|string|max:20|unique:vehiculos,placa,' . $id,
            'marca' => 'required|string|max:50',
            'modelo' => 'required|string|max:50',
            'conductor_id' => 'nullable|exists:usuarios,id',
            'img_url' => 'nullable|string|max:255',
        ]);

        $updated = $this->vehiculoRepository->update($id, $validated);

        if (!$updated) {
            return redirect()->route('vehiculos.index')
                ->with('error', 'Error al actualizar el vehículo.');
        }

        return redirect()->route('vehiculos.index')
            ->with('success', 'Vehículo actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vehiculo = $this->vehiculoRepository->find($id);

        if (!$vehiculo) {
            return redirect()->route('vehiculos.index')
                ->with('error', 'Vehículo no encontrado.');
        }

        // Verificar si tiene viajes asociados
        if ($vehiculo->viajes()->count() > 0) {
            return redirect()->route('vehiculos.index')
                ->with('error', 'No se puede eliminar el vehículo porque tiene viajes asociados.');
        }

        $this->vehiculoRepository->delete($id);

        return redirect()->route('vehiculos.index')
            ->with('success', 'Vehículo eliminado exitosamente.');
    }
}
