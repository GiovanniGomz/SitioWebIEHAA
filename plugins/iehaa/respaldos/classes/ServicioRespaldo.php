<?php

namespace Iehaa\Respaldos\Classes;

use Iehaa\Bitacora\Models\Bitacora;
use Iehaa\Usuarios\Models\Usuario;
use System\Models\Parameter;

/**
 * Respaldos de la base de datos PostgreSQL con pg_dump.
 *
 * Los archivos se guardan en storage/app/backups con el formato
 *   iehaa_YYYYMMDD_HHMMSS_{manual|auto}.dump
 * (formato "custom" de PostgreSQL: comprimido, se restaura con pg_restore).
 */
class ServicioRespaldo
{
    const PATRON_ARCHIVO = '/^iehaa_\d{8}_\d{6}_(manual|auto)\.dump$/';

    const FRECUENCIAS = [
        6   => 'Cada 6 horas',
        12  => 'Cada 12 horas',
        24  => 'Cada día (recomendado)',
        168 => 'Cada semana',
    ];

    const FRECUENCIA_DEFECTO = 24;
    const RETENCION_DEFECTO = 30;

    // ------------------------------------------------------------------
    // Ajustes (se guardan en la tabla de parámetros del sistema)
    // ------------------------------------------------------------------

    public static function ajustes(): array
    {
        return [
            'automatico' => (bool) Parameter::get('iehaa.respaldos.automatico', 1),
            'frecuencia' => (int) Parameter::get('iehaa.respaldos.frecuencia', self::FRECUENCIA_DEFECTO),
            'retencion'  => (int) Parameter::get('iehaa.respaldos.retencion', self::RETENCION_DEFECTO),
        ];
    }

    public static function guardarAjustes(bool $automatico, int $frecuencia, int $retencion): void
    {
        Parameter::set('iehaa.respaldos.automatico', $automatico ? 1 : 0);
        Parameter::set('iehaa.respaldos.frecuencia', $frecuencia);
        Parameter::set('iehaa.respaldos.retencion', $retencion);
    }

    // ------------------------------------------------------------------
    // Carpeta y listado
    // ------------------------------------------------------------------

    public static function directorio(): string
    {
        $dir = storage_path('app/backups');

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        // Defensa extra por si algún día se sirve la carpeta directamente:
        // .htaccess bloquea el acceso y el index.html evita que un servidor
        // con "Options Indexes" muestre el listado de archivos.
        $ht = $dir . DIRECTORY_SEPARATOR . '.htaccess';
        if (!is_file($ht)) {
            @file_put_contents($ht, "Require all denied\n");
        }

        $index = $dir . DIRECTORY_SEPARATOR . 'index.html';
        if (!is_file($index)) {
            @file_put_contents($index, '');
        }

        return $dir;
    }

    /** @return array<int, array{nombre:string,tipo:string,peso:int,fecha:\Carbon\Carbon}> */
    public static function listar(): array
    {
        $items = [];

        foreach (glob(self::directorio() . DIRECTORY_SEPARATOR . 'iehaa_*.dump') ?: [] as $ruta) {
            $nombre = basename($ruta);

            if (!preg_match(self::PATRON_ARCHIVO, $nombre, $m)) {
                continue;
            }

            $items[] = [
                'nombre' => $nombre,
                'tipo'   => $m[1],
                'peso'   => filesize($ruta) ?: 0,
                'fecha'  => \Carbon\Carbon::createFromTimestamp(filemtime($ruta)),
            ];
        }

        usort($items, fn ($a, $b) => $b['fecha']->timestamp <=> $a['fecha']->timestamp);

        return $items;
    }

    public static function rutaDe(string $nombre): ?string
    {
        if (!preg_match(self::PATRON_ARCHIVO, $nombre)) {
            return null;
        }

        $ruta = self::directorio() . DIRECTORY_SEPARATOR . $nombre;

        return is_file($ruta) ? $ruta : null;
    }

    public static function ultimoAutomatico(): ?\Carbon\Carbon
    {
        foreach (self::listar() as $r) {
            if ($r['tipo'] === 'auto') {
                return $r['fecha'];
            }
        }

        return null;
    }

    public static function formatearPeso(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }

        return number_format(max($bytes, 1) / 1024, 0) . ' KB';
    }

    // ------------------------------------------------------------------
    // Crear respaldo
    // ------------------------------------------------------------------

    /**
     * Genera un respaldo y devuelve su nombre de archivo.
     *
     * @throws \RuntimeException con un mensaje apto para mostrar al usuario
     */
    public static function crear(string $tipo = 'manual', ?Usuario $usuario = null): string
    {
        $tipo = $tipo === 'auto' ? 'auto' : 'manual';
        @set_time_limit(300);

        $dir = self::directorio();
        $lock = fopen($dir . DIRECTORY_SEPARATOR . '.lock', 'c');

        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            throw new \RuntimeException('Ya hay un respaldo en curso. Esperá unos segundos e intentá de nuevo.');
        }

        $nombre = 'iehaa_' . date('Ymd_His') . '_' . $tipo . '.dump';
        $ruta = $dir . DIRECTORY_SEPARATOR . $nombre;

        try {
            $cfg = \Db::connection()->getConfig();
            $pgDump = self::localizarPgDump();

            $proceso = proc_open(
                [
                    $pgDump,
                    '--host=' . $cfg['host'],
                    '--port=' . $cfg['port'],
                    '--username=' . $cfg['username'],
                    '--format=custom',
                    '--compress=6',
                    '--no-password',
                    '--file=' . $ruta,
                    $cfg['database'],
                ],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
                null,
                array_merge(getenv() ?: [], ['PGPASSWORD' => (string) ($cfg['password'] ?? '')]),
                ['bypass_shell' => true]
            );

            if (!is_resource($proceso)) {
                throw new \RuntimeException('No se pudo iniciar la herramienta de respaldo (pg_dump).');
            }

            stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $codigo = proc_close($proceso);

            if ($codigo !== 0 || !is_file($ruta) || filesize($ruta) < 100) {
                @unlink($ruta);
                \Log::error('[IEHAA] pg_dump falló (código ' . $codigo . '): ' . trim($error));
                throw new \RuntimeException('No se pudo generar el respaldo. Revisá que PostgreSQL esté activo y vuelva a intentarlo.');
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        self::aplicarRetencion();

        $quien = $tipo === 'auto' ? 'Respaldo automático' : 'Respaldo manual';
        Bitacora::registrar('respaldo', 'Respaldos', "{$quien} creado: {$nombre} (" . self::formatearPeso(filesize($ruta)) . ')', $usuario);

        return $nombre;
    }

    /** Conserva solo los N respaldos más recientes de cada tipo. */
    public static function aplicarRetencion(): void
    {
        $max = max(1, self::ajustes()['retencion']);
        $vistos = ['auto' => 0, 'manual' => 0];

        foreach (self::listar() as $r) {
            if (++$vistos[$r['tipo']] > $max) {
                @unlink(self::directorio() . DIRECTORY_SEPARATOR . $r['nombre']);
            }
        }
    }

    // ------------------------------------------------------------------
    // Automático
    // ------------------------------------------------------------------

    public static function debeCorrerAutomatico(): bool
    {
        $a = self::ajustes();

        if (!$a['automatico']) {
            return false;
        }

        $ultimo = self::ultimoAutomatico();

        // Margen de 30 min para que la tarea programada de las 02:00 no se
        // salte un día por diferencias de segundos.
        return !$ultimo || $ultimo->diffInSeconds(now()) >= ($a['frecuencia'] * 3600 - 1800);
    }

    /**
     * Respaldo "oportunista": si alguien usa el panel y ya toca respaldar,
     * lanza el comando en segundo plano sin hacer esperar la página. Sirve de
     * red de seguridad cuando la PC estaba apagada a la hora programada.
     */
    public static function dispararSiCorresponde(): void
    {
        try {
            // A lo sumo una comprobación cada 10 minutos.
            if (!\Cache::add('iehaa.respaldos.chequeo', 1, 600)) {
                return;
            }

            if (!self::debeCorrerAutomatico()) {
                return;
            }

            $php = self::localizarPhp();
            $artisan = base_path('artisan');

            if (DIRECTORY_SEPARATOR === '\\') {
                $cmd = 'cd /d "' . base_path() . '" && start /B "" "' . $php . '" "' . $artisan . '" iehaa:respaldar --auto > NUL 2>&1';
            } else {
                $cmd = 'cd "' . base_path() . '" && nohup "' . $php . '" "' . $artisan . '" iehaa:respaldar --auto > /dev/null 2>&1 &';
            }

            $h = popen($cmd, 'r');
            if (is_resource($h)) {
                pclose($h);
            }
        } catch (\Throwable $e) {
            \Log::warning('[IEHAA] No se pudo lanzar el respaldo automático: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------
    // Herramientas externas
    // ------------------------------------------------------------------

    public static function localizarPgDump(): string
    {
        return self::localizarHerramienta('pg_dump');
    }

    public static function localizarPgRestore(): string
    {
        return self::localizarHerramienta('pg_restore');
    }

    protected static function localizarHerramienta(string $nombre): string
    {
        $manual = env('PG_BIN_DIR');
        if ($manual) {
            foreach (['.exe', ''] as $ext) {
                $f = rtrim($manual, '\\/') . DIRECTORY_SEPARATOR . $nombre . $ext;
                if (is_file($f)) {
                    return $f;
                }
            }
        }

        $exe = DIRECTORY_SEPARATOR === '\\' ? '.exe' : '';
        $candidatos = [];

        foreach (['C:\\Program Files\\PostgreSQL', 'C:\\Program Files (x86)\\PostgreSQL'] as $base) {
            foreach (glob($base . '\\*\\bin\\' . $nombre . '.exe') ?: [] as $f) {
                $candidatos[] = $f;
            }
        }

        foreach (glob('/usr/lib/postgresql/*/bin/' . $nombre) ?: [] as $f) {
            $candidatos[] = $f;
        }

        if ($candidatos) {
            // Ideal: la misma versión mayor que el servidor (un pg_dump más
            // nuevo genera instrucciones que un servidor viejo no reconoce).
            // Si no hay, la versión más alta disponible.
            $mayor = self::versionMayorServidor();

            if ($mayor) {
                foreach ($candidatos as $f) {
                    if (str_contains(str_replace('\\', '/', $f), '/' . $mayor . '/bin/')) {
                        return $f;
                    }
                }
            }

            natsort($candidatos);
            return end($candidatos);
        }

        return $nombre . $exe; // confía en el PATH del sistema
    }

    protected static function versionMayorServidor(): ?int
    {
        try {
            $num = (int) \Db::selectOne('show server_version_num')->server_version_num;

            return $num ? intdiv($num, 10000) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected static function localizarPhp(): string
    {
        if (PHP_BINARY && preg_match('/php(\.exe)?$/i', PHP_BINARY)) {
            return PHP_BINARY;
        }

        $ini = php_ini_loaded_file();
        if ($ini) {
            $f = dirname($ini) . DIRECTORY_SEPARATOR . (DIRECTORY_SEPARATOR === '\\' ? 'php.exe' : 'php');
            if (is_file($f)) {
                return $f;
            }
        }

        return 'php';
    }
}
