<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;

class ActualizarMonedasUsuarios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usuarios:actualizar-monedas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar el campo moneda de usuarios existentes basándose en su país';

    // Mapeo de países a monedas
    private const MONEDAS_POR_PAIS = [
        'Bolivia' => 'BOB',
        'Argentina' => 'ARS',
        'Australia' => 'AUD',
        'Brasil' => 'BRL',
        'Canadá' => 'CAD',
        'Chile' => 'CLP',
        'China' => 'CNY',
        'Colombia' => 'COP',
        'Corea del Sur' => 'KRW',
        'Costa Rica' => 'CRC',
        'Dinamarca' => 'DKK',
        'España' => 'EUR',
        'Estados Unidos' => 'USD',
        'Francia' => 'EUR',
        'Alemania' => 'EUR',
        'Austria' => 'EUR',
        'Bélgica' => 'EUR',
        'Italia' => 'EUR',
        'Portugal' => 'EUR',
        'Andorra' => 'EUR',
        'Guatemala' => 'GTQ',
        'Honduras' => 'HNL',
        'India' => 'INR',
        'Japón' => 'JPY',
        'México' => 'MXN',
        'Nicaragua' => 'NIO',
        'Noruega' => 'NOK',
        'Paraguay' => 'PYG',
        'Perú' => 'PEN',
        'Reino Unido' => 'GBP',
        'República Dominicana' => 'DOP',
        'Rumania' => 'RON',
        'Rusia' => 'RUB',
        'Suecia' => 'SEK',
        'Suiza' => 'CHF',
        'Uruguay' => 'UYU',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Actualizando monedas de usuarios...');
        
        $usuarios = Usuario::whereNull('moneda')
            ->orWhere('moneda', '')
            ->get();
        
        if ($usuarios->isEmpty()) {
            $this->info('No hay usuarios para actualizar.');
            return Command::SUCCESS;
        }
        
        $actualizados = 0;
        
        foreach ($usuarios as $usuario) {
            $moneda = self::MONEDAS_POR_PAIS[$usuario->pais] ?? 'USD';
            $usuario->moneda = $moneda;
            $usuario->save();
            
            $this->line("Usuario {$usuario->nombre} {$usuario->apellido} ({$usuario->pais}): {$moneda}");
            $actualizados++;
        }
        
        $this->info("\n✅ Se actualizaron {$actualizados} usuarios exitosamente.");
        
        return Command::SUCCESS;
    }
}
