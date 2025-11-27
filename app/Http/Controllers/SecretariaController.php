<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SecretariaController extends Controller
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
        $secretarias = $this->usuarioRepository->findByRol('Secretaria');
        
        return Inertia::render('Secretarias/Index', [
            'secretarias' => $secretarias
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Secretarias/Create');
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
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB máximo
        ]);

        $validated['rol'] = 'Secretaria';
        $validated['password'] = Hash::make($validated['password']);

        // Manejar subida de imagen de perfil (opcional)
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            // Usaremos un ID temporal, luego lo actualizaremos después de crear el usuario
            $filename = 'secretaria-temp-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profiles', $filename, 'public');
            $validated['img_url'] = $path; // Solo guardar el path relativo: profiles/user-X.png
        }

        $usuario = $this->usuarioRepository->create($validated);

        // Si se subió una imagen, renombrarla con el ID real del usuario
        if ($request->hasFile('avatar') && isset($validated['img_url'])) {
            $oldPath = $validated['img_url']; // Ya es un path relativo
            $newFilename = 'user-' . $usuario->id . '-' . time() . '.' . $request->file('avatar')->getClientOriginalExtension();
            $newPath = 'profiles/' . $newFilename;
            
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->move($oldPath, $newPath);
                $usuario->img_url = $newPath; // Guardar solo el path relativo
                $usuario->save();
            }
        }

        return redirect()->route('secretarias.index')
            ->with('success', 'Secretaria registrada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $secretaria = $this->usuarioRepository->find($id);

        if (!$secretaria || $secretaria->rol !== 'Secretaria') {
            return redirect()->route('secretarias.index')
                ->with('error', 'Secretaria no encontrada.');
        }

        return Inertia::render('Secretarias/Show', [
            'secretaria' => $secretaria
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $secretaria = $this->usuarioRepository->find($id);

        if (!$secretaria || $secretaria->rol !== 'Secretaria') {
            return redirect()->route('secretarias.index')
                ->with('error', 'Secretaria no encontrada.');
        }

        return Inertia::render('Secretarias/Edit', [
            'secretaria' => $secretaria
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $secretaria = $this->usuarioRepository->find($id);

        if (!$secretaria || $secretaria->rol !== 'Secretaria') {
            return redirect()->route('secretarias.index')
                ->with('error', 'Secretaria no encontrada.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'ci' => 'required|string|max:20|unique:usuarios,ci,' . $id,
            'telefono' => 'nullable|string|max:20',
            'correo' => 'required|email|max:255|unique:usuarios,correo,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB máximo
        ]);

        // Solo actualizar password si se proporciona
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Manejar subida de imagen de perfil (opcional)
        if ($request->hasFile('avatar')) {
            // Eliminar imagen anterior si existe
            if ($secretaria->img_url) {
                // Limpiar el path por si tiene /storage/ (compatibilidad con registros antiguos)
                $oldPath = $secretaria->img_url;
                if (str_starts_with($oldPath, '/storage/')) {
                    $oldPath = substr($oldPath, 9); // Remover '/storage/'
                }
                $oldPath = ltrim($oldPath, '/');
                
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Guardar nueva imagen
            $file = $request->file('avatar');
            $filename = 'user-' . $secretaria->id . '-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profiles', $filename, 'public');
            $validated['img_url'] = $path; // Solo guardar el path relativo: profiles/user-X.png
        }

        $this->usuarioRepository->update($id, $validated);

        return redirect()->route('secretarias.index')
            ->with('success', 'Secretaria actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $secretaria = $this->usuarioRepository->find($id);

        if (!$secretaria || $secretaria->rol !== 'Secretaria') {
            return redirect()->route('secretarias.index')
                ->with('error', 'Secretaria no encontrada.');
        }

        $this->usuarioRepository->delete($id);

        return redirect()->route('secretarias.index')
            ->with('success', 'Secretaria eliminada exitosamente.');
    }
}

