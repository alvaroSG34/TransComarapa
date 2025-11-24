<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\VehiculoRepositoryInterface;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB máximo
        ]);

        // Manejar subida de imagen de vehículo (opcional)
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'vehiculo-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('vehiculos', $filename, 'public');
            $validated['img_url'] = '/storage/' . $path;
        }

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
        $vehiculo = $this->vehiculoRepository->find($id);

        $validated = $request->validate([
            'placa' => 'required|string|max:20|unique:vehiculos,placa,' . $id,
            'marca' => 'required|string|max:50',
            'modelo' => 'required|string|max:50',
            'conductor_id' => 'nullable|exists:usuarios,id',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB máximo
        ]);

        // Manejar subida de imagen de vehículo (opcional)
        if ($request->hasFile('avatar')) {
            // Eliminar imagen anterior si existe (solo si es local)
            if ($vehiculo->img_url && str_contains($vehiculo->img_url, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $vehiculo->img_url);
                $oldPath = ltrim($oldPath, '/');
                if ($oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Guardar nueva imagen
            $file = $request->file('avatar');
            $filename = 'vehiculo-' . $id . '-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('vehiculos', $filename, 'public');
            $validated['img_url'] = '/storage/' . $path;
        }

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
