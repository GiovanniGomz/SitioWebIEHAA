<?php

namespace Iehaa\Respaldos\Console;

use Iehaa\Bitacora\Models\Bitacora;
use Iehaa\Respaldos\Classes\ServicioRespaldo;
use Illuminate\Console\Command;

/**
 * Restaura la base de datos desde un respaldo. Es una operación destructiva
 * (reemplaza los datos actuales), por eso solo existe por consola y pide
 * confirmación. Antes de restaurar se hace un respaldo de seguridad.
 */
class Restaurar extends Command
{
    protected $name = 'iehaa:restaurar';

    protected $description = 'Restaura la base de datos desde un respaldo de storage/app/backups (REEMPLAZA los datos actuales).';

    protected $signature = 'iehaa:restaurar {archivo : Nombre del respaldo, por ejemplo iehaa_20260101_020000_auto.dump} {--force : No pedir confirmación}';

    public function handle()
    {
        $ruta = ServicioRespaldo::rutaDe((string) $this->argument('archivo'));

        if (!$ruta) {
            $this->error('No existe ese respaldo. Los disponibles son:');
            foreach (ServicioRespaldo::listar() as $r) {
                $this->line('  ' . $r['nombre']);
            }
            return 1;
        }

        if (!$this->option('force') && !$this->confirm('Esto REEMPLAZA los datos actuales con los del respaldo. ¿Continuar?')) {
            return 1;
        }

        try {
            $seguridad = ServicioRespaldo::crear('manual');
            $this->info('Respaldo de seguridad previo: ' . $seguridad);
        } catch (\Throwable $e) {
            $this->error('No se restauró nada: ' . $e->getMessage());
            return 1;
        }

        $cfg = \Db::connection()->getConfig();

        $proceso = proc_open(
            [
                ServicioRespaldo::localizarPgRestore(),
                '--host=' . $cfg['host'],
                '--port=' . $cfg['port'],
                '--username=' . $cfg['username'],
                '--dbname=' . $cfg['database'],
                '--clean', '--if-exists', '--no-owner', '--no-password',
                $ruta,
            ],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            null,
            array_merge(getenv() ?: [], ['PGPASSWORD' => (string) ($cfg['password'] ?? '')]),
            ['bypass_shell' => true]
        );

        stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        $codigo = proc_close($proceso);

        if ($codigo !== 0) {
            $this->warn('pg_restore terminó con avisos (código ' . $codigo . '):');
            $this->line(trim($error));
        }

        Bitacora::registrar('respaldo', 'Respaldos', 'Se restauró la base de datos desde ' . basename($ruta));
        $this->info('Restauración finalizada.');

        return $codigo === 0 ? 0 : 1;
    }
}
