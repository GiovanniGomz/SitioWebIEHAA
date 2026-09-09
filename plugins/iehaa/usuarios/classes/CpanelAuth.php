<?php

namespace Iehaa\Usuarios\Classes;

use Iehaa\Usuarios\Models\Usuario;

/**
 * Small session-based auth helper for the custom "cpanel" admin area.
 * Not related to Winter's Backend module auth.
 */
class CpanelAuth
{
    const SESSION_KEY = 'cpanel_usuario_id';

    public static function attempt(string $email, string $password): ?Usuario
    {
        $usuario = Usuario::where('email', $email)->where('activo', true)->first();

        if (!$usuario || !$usuario->checkHashValue('password', $password)) {
            return null;
        }

        session()->put(self::SESSION_KEY, $usuario->id);

        return $usuario;
    }

    public static function usuario(): ?Usuario
    {
        $id = session()->get(self::SESSION_KEY);

        if (!$id) {
            return null;
        }

        return Usuario::find($id);
    }

    public static function check(): bool
    {
        return self::usuario() !== null;
    }

    public static function esAdmin(): bool
    {
        $usuario = self::usuario();

        return $usuario && $usuario->esAdmin();
    }

    public static function logout(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
