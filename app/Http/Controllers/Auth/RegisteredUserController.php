<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'ci' => 'required|string|max:20|unique:usuarios,ci',
            'telefono' => 'required|string|max:20',
            'email' => 'required|string|lowercase|email|max:255|unique:usuarios,correo',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        
        $user = Usuario::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'ci' => $request->ci,
            'telefono' => $request->telefono,
            'correo' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => 'Cliente', // Rol por defecto
            'tema_preferido' => 'jovenes', // Tema por defecto
            'modo_contraste' => 'normal',
            'tamano_fuente' => 'mediano',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
