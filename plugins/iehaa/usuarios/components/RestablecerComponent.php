<?php

namespace Iehaa\Usuarios\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Usuarios\Classes\CpanelAuth;
use Iehaa\Usuarios\Models\Usuario;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

/**
 * Segundo paso de "olvidé mi contraseña": consume el enlace de un solo uso
 * y permite elegir una nueva contraseña.
 */
class RestablecerComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'restablecerComponent',
            'description' => 'Restablecer contraseña con un enlace de un solo uso'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        if (CpanelAuth::check()) {
            return redirect('/dashboard');
        }

        $email = get('email', '');
        $token = get('token', '');

        $this->page['email'] = $email;
        $this->page['token'] = $token;
        $this->page['enlaceValido'] = $this->buscarUsuario($email, $token) !== null;
    }

    public function onRestablecer()
    {
        $data = Input::all();

        $validator = Validator::make($data, [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ], [
            'email.required' => '* Campo obligatorio.',
            'token.required' => '* Campo obligatorio.',
            'password.required' => '* Campo obligatorio.',
            'password.min' => 'Mínimo 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $usuario = $this->buscarUsuario($data['email'], $data['token']);

        if (!$usuario) {
            throw new ValidationException([
                'password' => 'Este enlace ya no es válido. Solicitá uno nuevo.',
            ]);
        }

        $usuario->password = $data['password'];
        $usuario->reset_token = null;
        $usuario->reset_token_expira = null;
        $usuario->save();

        return [
            'estado' => 'exito',
            'mensaje' => '¡Contraseña actualizada correctamente! Ya podés iniciar sesión.',
        ];
    }

    /**
     * Busca al usuario dueño de un enlace de restablecimiento válido y
     * todavía no vencido. Devuelve null si el enlace no existe, ya se usó o
     * expiró.
     */
    private function buscarUsuario(string $email, string $token): ?Usuario
    {
        if ($email === '' || $token === '') {
            return null;
        }

        $usuario = Usuario::where('email', trim($email))
            ->where('activo', true)
            ->whereNotNull('reset_token')
            ->first();

        if (!$usuario || !$usuario->reset_token_expira || $usuario->reset_token_expira->isPast()) {
            return null;
        }

        if (!hash_equals($usuario->reset_token, hash('sha256', $token))) {
            return null;
        }

        return $usuario;
    }
}
