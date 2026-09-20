<?php

namespace Iehaa\Respaldos\Console;

use Iehaa\Respaldos\Classes\ServicioRespaldo;
use Illuminate\Console\Command;

class Respaldar extends Command
{
    protected $name = 'iehaa:respaldar';

    protected $description = 'Genera un respaldo de la base de datos en storage/app/backups.';

    protected $signature = 'iehaa:respaldar {--auto : Respaldo programado: solo se hace si ya toca según la frecuencia configurada} {--forzar : Ignora la frecuencia y respalda ahora}';

    public function handle()
    {
        $esAuto = $this->option('auto');

        if ($esAuto && !$this->option('forzar') && !ServicioRespaldo::debeCorrerAutomatico()) {
            $this->info('Todavía no toca un respaldo automático (o está desactivado).');
            return 0;
        }

        try {
            $nombre = ServicioRespaldo::crear($esAuto ? 'auto' : 'manual');
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return 1;
        }

        $this->info('Respaldo creado: ' . $nombre);
        return 0;
    }
}
