<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$rutas = DB::table('visitas')
    ->select('ruta', DB::raw('COUNT(*) as total'))
    ->groupBy('ruta')
    ->orderBy('ruta')
    ->limit(30)
    ->get();

echo "Rutas registradas en la base de datos:\n\n";
foreach ($rutas as $ruta) {
    echo sprintf("%-30s %d visitas\n", $ruta->ruta, $ruta->total);
}

echo "\n¿La ruta '/' existe? ";
$raiz = DB::table('visitas')->where('ruta', '/')->count();
echo $raiz > 0 ? "SÍ ($raiz visitas)" : "NO";
echo "\n";
