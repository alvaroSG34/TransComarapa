<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\RutaRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RutaController extends Controller
{
    protected $rutaRepository;

    public function __construct(RutaRepositoryInterface $rutaRepository)
    {
        $this->rutaRepository = $rutaRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rutas = $this->rutaRepository->all();
        
        return Inertia::render('Rutas/Index', [
            'rutas' => $rutas
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Rutas/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'origen' => 'required|string|max:100',
            'destino' => 'required|string|max:100',
            'nombre' => 'required|string|max:255',
            'pais_operacion' => 'required|string|max:100',
            'moneda' => 'required|string|size:3',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Máximo 5MB
        ]);

        // Manejar la carga de la imagen
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('rutas', 'public');
            $validated['imagen'] = $path;
        }

        $this->rutaRepository->create($validated);

        return redirect()->route('rutas.index')
            ->with('success', 'Ruta creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ruta = $this->rutaRepository->find($id);

        if (!$ruta) {
            return redirect()->route('rutas.index')
                ->with('error', 'Ruta no encontrada.');
        }

        return Inertia::render('Rutas/Show', [
            'ruta' => $ruta
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $ruta = $this->rutaRepository->find($id);

        if (!$ruta) {
            return redirect()->route('rutas.index')
                ->with('error', 'Ruta no encontrada.');
        }

        return Inertia::render('Rutas/Edit', [
            'ruta' => $ruta
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'origen' => 'required|string|max:100',
            'destino' => 'required|string|max:100',
            'nombre' => 'required|string|max:255',
            'pais_operacion' => 'required|string|max:100',
            'moneda' => 'required|string|size:3',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Máximo 5MB
        ]);

        // Manejar la carga de la nueva imagen
        if ($request->hasFile('imagen')) {
            // Eliminar la imagen anterior si existe
            $ruta = $this->rutaRepository->find($id);
            if ($ruta && $ruta->imagen) {
                \Storage::disk('public')->delete($ruta->imagen);
            }
            
            $path = $request->file('imagen')->store('rutas', 'public');
            $validated['imagen'] = $path;
        }

        $updated = $this->rutaRepository->update($id, $validated);

        if (!$updated) {
            return redirect()->route('rutas.index')
                ->with('error', 'Error al actualizar la ruta.');
        }

        return redirect()->route('rutas.index')
            ->with('success', 'Ruta actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deleted = $this->rutaRepository->delete($id);

        if (!$deleted) {
            return redirect()->route('rutas.index')
                ->with('error', 'Error al eliminar la ruta.');
        }

        return redirect()->route('rutas.index')
            ->with('success', 'Ruta eliminada exitosamente.');
    }
}
