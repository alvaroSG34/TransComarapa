<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Buscar usuario por correo (no por email)
        $user = \App\Models\Usuario::where('correo', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['No encontramos ningún usuario con ese correo electrónico.'],
            ]);
        }

        // Enviar enlace de reset usando el correo del usuario
        $status = Password::sendResetLink(
            ['email' => $user->correo]
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', 'Le hemos enviado por correo el enlace para restablecer su contraseña.');
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
