<?php

namespace Iehaa\Bitacora\Models;

use Iehaa\Usuarios\Classes\CpanelAuth;
use Iehaa\Usuarios\Models\Usuario;
use Winter\Storm\Database\Model;

class Bitacora extends Model
{
    public $table = 'data.bitacora';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $dates = ['created_at'];

    /** Acciones conocidas y su etiqueta visible. */
    const ACCIONES = [
        'crear'          => 'Creó',
        'modificar'      => 'Modificó',
        'eliminar'       => 'Eliminó',
        'login'          => 'Inicio de sesión',
        'login_fallido'  => 'Inicio de sesión fallido',
        'logout'         => 'Cierre de sesión',
        'password'       => 'Contraseña',
        'respaldo'       => 'Respaldo',
        'descarga'       => 'Descarga',
        'reporte'        => 'Reporte',
    ];

    public function getAccionEtiquetaAttribute(): string
    {
        return self::ACCIONES[$this->accion] ?? ucfirst($this->accion);
    }

    /**
     * Registra una acción. NUNCA lanza excepciones: una falla de la bitácora
     * no debe romper la operación que se está registrando.
     */
    public static function registrar(string $accion, string $modulo, string $descripcion, ?Usuario $usuario = null): void
    {
        try {
            $usuario = $usuario ?: CpanelAuth::usuario();

            self::create([
                'usuario_id'     => $usuario?->id,
                'usuario_nombre' => $usuario?->nombre,
                'usuario_email'  => $usuario?->email,
                'accion'         => $accion,
                'modulo'         => $modulo,
                'descripcion'    => mb_substr($descripcion, 0, 1000),
                'ip'             => request()?->ip(),
                'created_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('[IEHAA] No se pudo escribir en la bitácora: ' . $e->getMessage());
        }
    }
}
