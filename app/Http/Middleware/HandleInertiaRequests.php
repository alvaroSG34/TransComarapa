<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Detectar si es día o noche según horario de Bolivia (6 AM - 6 PM)
        $currentHour = now()->setTimezone('America/La_Paz')->hour;
        $timeMode = ($currentHour >= 6 && $currentHour < 18) ? 'day' : 'night';

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'timeMode' => $timeMode,
            'currentHour' => $currentHour,
        ];
    }
}
