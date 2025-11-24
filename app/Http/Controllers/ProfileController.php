<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Manejar datos del formulario (name y email)
        $validated = $request->validated();
        
        // Convertir 'name' a 'nombre' y 'apellido' si es necesario
        // O mantener la estructura actual si ya usas name directamente
        if (isset($validated['name'])) {
            $parts = explode(' ', $validated['name'], 2);
            $user->nombre = $parts[0] ?? '';
            $user->apellido = $parts[1] ?? '';
        }
        
        if (isset($validated['email'])) {
            $user->correo = $validated['email'];
            if ($user->isDirty('correo')) {
                $user->email_verified_at = null;
            }
        }

        // Manejar subida de imagen de perfil
        if ($request->hasFile('avatar')) {
            // Eliminar imagen anterior si existe (solo si es local)
            if ($user->img_url && str_contains($user->img_url, '/storage/')) {
                // Extraer el path relativo desde la URL
                $oldPath = str_replace('/storage/', '', $user->img_url);
                $oldPath = ltrim($oldPath, '/');
                if ($oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Guardar nueva imagen
            $file = $request->file('avatar');
            $filename = 'user-' . $user->id . '-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profiles', $filename, 'public');
            
            // Guardar URL en la base de datos
            $user->img_url = '/storage/' . $path;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
