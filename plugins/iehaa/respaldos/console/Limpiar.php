<?php

namespace Iehaa\Respaldos\Console;

use Iehaa\Bitacora\Models\Bitacora;
use Iehaa\Respaldos\Classes\ServicioRespaldo;
use Iehaa\Usuarios\Models\Usuario;
use Illuminate\Console\Command;

/**
 * Deja el sistema en "cero" para pasar a producción: borra todos los
 * registros de contenido (investigadores, publicaciones, fondos
 * documentales, CEDJAG, inventario, etc.) y los usuarios adicionales,
 * conservando SOLO la cuenta de administrador original y la Configuración
 * general del sitio (nombre, contacto, redes sociales, textos, fondo de
 * pantalla), que ya tiene los datos reales del instituto.
 *
 * Es una operación destructiva: por eso pide confirmación (salvo --forzar)
 * y siempre crea un respaldo de seguridad antes de borrar nada.
 */
class Limpiar extends Command
{
    protected $name = 'iehaa:limpiar';

    protected $description = 'Borra todos los datos de contenido y usuarios adicionales, dejando solo el administrador y la Configuración. Pensado para preparar el sistema antes de subirlo a producción.';

    protected $signature = 'iehaa:limpiar {--forzar : No pedir confirmación} {--sin-respaldo : No crear el respaldo de seguridad previo (no recomendado)}';

    /**
     * Tablas de contenido a vaciar por completo. Ninguna tabla de sistema
     * (migraciones, sesiones, config de Winter) vive en el esquema "data",
     * así que este listado es seguro sin tocarlas.
     */
    const TABLAS_CONTENIDO = [
        'facultades', 'tipo_investigadores', 'categoria_investigadores', 'tipo_publicaciones',
        'categorias_correspondencia', 'investigadores', 'proyectos', 'publicaciones', 'documentos',
        'expedientes', 'activo_fijo', 'activo_fijo_archivos', 'anuncios', 'mensajes_contacto',
        'solicitudes', 'correspondencia', 'registro_correspondencia',
        'archiveros', 'gavetas', 'carpetas', 'folders', 'fabio',
        'estantes', 'anaqueles', 'colecciones', 'fondo',
        'bitacora',
    ];

    /** Carpetas de storage con archivos subidos por esos módulos. */
    const CARPETAS_SUBIDAS = [
        'activo_fijo', 'anuncios', 'documentos', 'expedientes', 'fabio', 'fondo',
        'proyectos', 'publicaciones', 'registro-correspondencia',
    ];

    public function handle()
    {
        $admin = Usuario::orderBy('id')->first();

        if (!$admin) {
            $this->error('No hay ningún usuario en el sistema — no se puede determinar cuál conservar. Se cancela.');
            return 1;
        }

        $otrosUsuarios = Usuario::where('id', '!=', $admin->id)->count();

        $this->warn('Esto va a:');
        $this->line('  - Vaciar todos los módulos de contenido (investigadores, publicaciones, fondos documentales, CEDJAG, correspondencia, inventario, anuncios, mensajes, bitácora, etc.)');
        $this->line('  - Eliminar los archivos subidos de esos módulos (documentos, imágenes, adjuntos)');
        $this->line("  - Eliminar los otros {$otrosUsuarios} usuario(s), conservando SOLO: {$admin->nombre} <{$admin->email}>");
        $this->line('  - NO toca la Configuración general del sitio ni la carpeta de respaldos.');

        if (!$this->option('forzar') && !$this->confirm('¿Confirmás que querés continuar?')) {
            $this->info('Cancelado, no se cambió nada.');
            return 1;
        }

        if (!$this->option('sin-respaldo')) {
            try {
                $nombre = ServicioRespaldo::crear('manual');
                $this->info('Respaldo de seguridad previo: ' . $nombre);
            } catch (\Throwable $e) {
                $this->error('No se limpió nada: no se pudo crear el respaldo de seguridad (' . $e->getMessage() . ').');
                return 1;
            }
        }

        \Db::statement(
            'TRUNCATE TABLE ' . implode(',', array_map(fn ($t) => 'data.' . $t, self::TABLAS_CONTENIDO)) . ' RESTART IDENTITY CASCADE'
        );
        $this->info('Tablas de contenido vaciadas.');

        $eliminados = Usuario::where('id', '!=', $admin->id)->delete();
        $this->info("Usuarios eliminados: {$eliminados}.");

        $this->limpiarArchivosSubidos($admin);
        $this->info('Archivos subidos eliminados (se conservó la foto de perfil del administrador y todo lo de Configuración).');

        Bitacora::registrar(
            'eliminar',
            'Sistema',
            'Se preparó la base de datos para producción: se limpiaron los módulos de contenido y los usuarios adicionales, conservando la cuenta de administrador y la Configuración del sitio.',
            $admin
        );

        $this->info('¡Listo! El sistema quedó con la cuenta de administrador, la Configuración del sitio, y todo lo demás vacío.');
        return 0;
    }

    private function limpiarArchivosSubidos(Usuario $admin): void
    {
        $base = base_path('storage/app/uploads/public');

        foreach (self::CARPETAS_SUBIDAS as $carpeta) {
            $ruta = $base . '/' . $carpeta;

            if (!is_dir($ruta)) {
                continue;
            }

            foreach (glob($ruta . '/*') ?: [] as $archivo) {
                if (is_file($archivo)) {
                    @unlink($archivo);
                }
            }
        }

        // La carpeta de perfiles es especial: se conserva únicamente la foto
        // del administrador que sigue existiendo (si tiene una).
        $carpetaPerfiles = $base . '/perfiles';

        if (is_dir($carpetaPerfiles)) {
            foreach (glob($carpetaPerfiles . '/*') ?: [] as $archivo) {
                if (is_file($archivo) && basename($archivo) !== $admin->foto) {
                    @unlink($archivo);
                }
            }
        }

        // La carpeta de configuración (logo, fondo de pantalla) no se toca.
    }
}
