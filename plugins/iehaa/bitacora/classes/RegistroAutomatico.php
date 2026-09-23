<?php

namespace Iehaa\Bitacora\Classes;

use Iehaa\Bitacora\Models\Bitacora;
use Iehaa\Usuarios\Classes\CpanelAuth;
use Iehaa\Usuarios\Models\Usuario;

/**
 * Registra en la bitácora, sin tocar cada componente, las altas,
 * modificaciones y eliminaciones de los modelos importantes del sistema.
 */
class RegistroAutomatico
{
    /** clase del modelo => [nombre del módulo, artículo + etiqueta singular] */
    const MODELOS = [
        \Iehaa\Usuarios\Models\Usuario::class                              => ['Usuarios', 'el usuario'],
        \Iehaa\Configuracion\Models\Configuracion::class                   => ['Configuración', 'la configuración del sitio'],
        \Iehaa\Anuncios\Models\Anuncio::class                              => ['Anuncios', 'el anuncio'],
        \Iehaa\Investigadores\Models\Investigador::class                   => ['Investigadores', 'el investigador'],
        \Iehaa\Proyectos\Models\Proyecto::class                            => ['Proyectos', 'el proyecto'],
        \Iehaa\Publicaciones\Models\Publicacion::class                     => ['Publicaciones', 'la publicación'],
        \IEHAA\Documentos\Models\Documento::class                          => ['Documentos', 'el documento'],
        \Iehaa\Solicitudes\Models\Solicitud::class                         => ['Solicitudes CEDJAG', 'la solicitud'],
        \Iehaa\Correspondencia\Models\Correspondencia::class               => ['Préstamo CEDJAG', 'el préstamo aceptado'],
        \Iehaa\Registrocorrespondencia\Models\RegistroCorrespondencia::class => ['Correspondencia', 'el registro de correspondencia'],
        \Iehaa\Categoriacorrespondencia\Models\CategoriaCorrespondencia::class => ['Categorías de correspondencia', 'la categoría'],
        \Iehaa\Inventario\Models\ActivoFijo::class                         => ['Inventario de activo fijo', 'el activo fijo'],
        \Iehaa\Gestiondocumental\Models\Expediente::class                  => ['Gestión documental', 'el expediente'],
        \Iehaa\Mensajescontacto\Models\MensajeContacto::class              => ['Mensajes de contacto', 'el mensaje'],
        \Iehaa\Facultades\Models\Facultad::class                           => ['Facultades', 'la facultad'],
        \Iehaa\Tipoinvestigadores\Models\TipoInvestigador::class           => ['Tipos de investigador', 'el tipo de investigador'],
        \Iehaa\Categoriainvestigadores\Models\CategoriaInvestigador::class => ['Categorías de investigador', 'la categoría'],
        \Iehaa\Tipopublicaciones\Models\TipoPublicaciones::class           => ['Tipos de publicación', 'el tipo de publicación'],
        \Iehaa\Fondo\Models\Fondo::class                                   => ['Fondo Bibliográfico', 'el documento del fondo'],
        \Iehaa\Fabio\Models\Fabio::class                                   => ['Fabio Castillo', 'el documento de Fabio Castillo'],
        \Iehaa\Coleccion\Models\Coleccion::class                           => ['Fondo Bibliográfico', 'la colección'],
        \Iehaa\Anaquel\Models\Anaquel::class                               => ['Fondo Bibliográfico', 'el anaquel'],
        \Iehaa\Estante\Models\Estante::class                               => ['Fondo Bibliográfico', 'el estante'],
        \Iehaa\Archiveros\Models\Archivero::class                          => ['Fabio Castillo', 'el archivero'],
        \Iehaa\Gavetas\Models\Gaveta::class                                => ['Fabio Castillo', 'la gaveta'],
        \Iehaa\Carpetas\Models\Carpeta::class                              => ['Fabio Castillo', 'la carpeta'],
        \Iehaa\Folders\Models\Folder::class                                => ['Fabio Castillo', 'el folder'],
    ];

    /** Columnas que identifican al registro, en orden de preferencia. */
    const CAMPOS_NOMBRE = [
        'nombre', 'titulo', 'asunto', 'descripcion_bien', 'nombre_solicitante',
        'remitente', 'numero_inventario', 'codigo', 'serie',
    ];

    /** Campos cuyo cambio de valor (antes → después) interesa ver en la bitácora. */
    const CAMPOS_CON_VALOR = ['estado', 'estado_bien', 'rol', 'activo', 'enviado_email'];

    /** Cambios que no aportan información a la bitácora. */
    const CAMPOS_IGNORADOS = ['updated_at', 'created_at', 'reset_token', 'reset_token_expira', 'leido'];

    public static function escuchar(): void
    {
        foreach (self::MODELOS as $clase => [$modulo, $etiqueta]) {
            if (!class_exists($clase)) {
                continue;
            }

            $clase::created(function ($modelo) use ($modulo, $etiqueta) {
                self::registrarCambio('crear', $modelo, $modulo, $etiqueta);
            });

            $clase::updated(function ($modelo) use ($modulo, $etiqueta) {
                self::registrarCambio('modificar', $modelo, $modulo, $etiqueta);
            });

            $clase::deleted(function ($modelo) use ($modulo, $etiqueta) {
                self::registrarCambio('eliminar', $modelo, $modulo, $etiqueta);
            });
        }
    }

    protected static function registrarCambio(string $accion, $modelo, string $modulo, string $etiqueta): void
    {
        try {
            $usuario = CpanelAuth::usuario();
            $nombre = self::nombreDe($modelo);
            $verbo = ['crear' => 'Creó', 'modificar' => 'Modificó', 'eliminar' => 'Eliminó'][$accion];
            $detalle = '';

            if ($accion === 'modificar') {
                $cambios = array_values(array_diff(array_keys($modelo->getChanges()), self::CAMPOS_IGNORADOS));

                if (!$cambios) {
                    return;
                }

                // Solo cambió la contraseña (cambio desde el perfil o por
                // enlace de recuperación): se registra como acción propia.
                if ($cambios === ['password']) {
                    if (!$usuario && $modelo instanceof Usuario) {
                        $usuario = $modelo;
                    }
                    Bitacora::registrar('password', $modulo, "Cambió la contraseña de {$nombre}", $usuario);
                    return;
                }

                // En campos de estado se muestra el valor anterior y el nuevo.
                $legibles = array_map(function ($c) use ($modelo) {
                    if ($c === 'password') {
                        return 'contraseña';
                    }

                    $etiqueta = str_replace('_', ' ', $c);

                    if (in_array($c, self::CAMPOS_CON_VALOR, true)) {
                        $antes = self::valorLegible($modelo->getOriginal($c));
                        $despues = self::valorLegible($modelo->getAttribute($c));
                        return "{$etiqueta}: {$antes} → {$despues}";
                    }

                    return $etiqueta;
                }, $cambios);
                $detalle = ' (campos: ' . implode(', ', array_slice($legibles, 0, 8)) . ')';
            }

            // Formulario de contacto / solicitud de CEDJAG: llegan sin sesión.
            $publico = !$usuario && $accion === 'crear' && (
                $modelo instanceof \Iehaa\Mensajescontacto\Models\MensajeContacto
                || $modelo instanceof \Iehaa\Solicitudes\Models\Solicitud
            );

            $texto = $publico
                ? "Se recibió {$etiqueta} «{$nombre}» desde el sitio público"
                : "{$verbo} {$etiqueta} «{$nombre}»{$detalle}";

            Bitacora::registrar($accion, $modulo, $texto, $usuario);
        } catch (\Throwable $e) {
            \Log::warning('[IEHAA] Bitácora automática falló: ' . $e->getMessage());
        }
    }

    protected static function valorLegible($valor): string
    {
        if (is_bool($valor)) {
            return $valor ? 'sí' : 'no';
        }

        return $valor === null || $valor === '' ? '—' : mb_strimwidth((string) $valor, 0, 40, '…');
    }

    protected static function nombreDe($modelo): string
    {
        $nombre = $modelo->getAttribute('nombre');
        $apellido = $modelo->getAttribute('apellido');

        if (is_string($nombre) && trim($nombre) !== '' && is_string($apellido) && trim($apellido) !== '') {
            return mb_strimwidth(trim($nombre) . ' ' . trim($apellido), 0, 90, '…');
        }

        foreach (self::CAMPOS_NOMBRE as $campo) {
            $valor = $modelo->getAttribute($campo);

            if (is_string($valor) && trim($valor) !== '') {
                return mb_strimwidth(trim($valor), 0, 90, '…');
            }
        }

        return '#' . $modelo->getKey();
    }
}
