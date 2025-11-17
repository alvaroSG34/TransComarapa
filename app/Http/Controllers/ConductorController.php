<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ConductorController extends Controller
{
    protected $usuarioRepository;

    public function __construct(UsuarioRepositoryInterface $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $conductores = $this->usuarioRepository->findByRol('Conductor');
        
        return Inertia::render('Conductores/Index', [
            'conductores' => $conductores
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Conductores/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'ci' => 'required|string|max:20|unique:usuarios,ci',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'required|email|max:255|unique:usuarios,correo',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['rol'] = 'Conductor';
        $validated['password'] = Hash::make($validated['password']);

        $this->usuarioRepository->create($validated);

        return redirect()->route('conductores.index')
            ->with('success', 'Conductor registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $conductor = $this->usuarioRepository->find($id);

        if (!$conductor || $conductor->rol !== 'Conductor') {
            return redirect()->route('conductores.index')
                ->with('error', 'Conductor no encontrado.');
        }

        return Inertia::render('Conductores/Show', [
            'conductor' => $conductor
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $conductor = $this->usuarioRepository->find($id);

        if (!$conductor || $conductor->rol !== 'Conductor') {
            return redirect()->route('conductores.index')
                ->with('error', 'Conductor no encontrado.');
        }

        return Inertia::render('Conductores/Edit', [
            'conductor' => $conductor
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $conductor = $this->usuarioRepository->find($id);

        if (!$conductor || $conductor->rol !== 'Conductor') {
            return redirect()->route('conductores.index')
                ->with('error', 'Conductor no encontrado.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'ci' => 'required|string|max:20|unique:usuarios,ci,' . $id,
            'telefono' => 'nullable|string|max:20',
            'correo' => 'required|email|max:255|unique:usuarios,correo,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Solo actualizar password si se proporciona
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $this->usuarioRepository->update($id, $validated);

        return redirect()->route('conductores.index')
            ->with('success', 'Conductor actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $conductor = $this->usuarioRepository->find($id);

        if (!$conductor || $conductor->rol !== 'Conductor') {
            return redirect()->route('conductores.index')
                ->with('error', 'Conductor no encontrado.');
        }

        // Verificar si tiene vehículos asignados
        if ($conductor->vehiculos()->count() > 0) {
            return redirect()->route('conductores.index')
                ->with('error', 'No se puede eliminar el conductor porque tiene vehículos asignados.');
        }

        $this->usuarioRepository->delete($id);

        return redirect()->route('conductores.index')
            ->with('success', 'Conductor eliminado exitosamente.');
    }
}
