<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('LandingPage', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rutas para preferencias de tema
    Route::post('/api/user/theme-preferences', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'tema_preferido' => 'sometimes|in:ninos,jovenes,adultos',
            'modo_contraste' => 'sometimes|in:normal,alto',
        ]);

        $request->user()->update($validated);

        return response()->json(['success' => true]);
    });
});

require __DIR__.'/auth.php';
