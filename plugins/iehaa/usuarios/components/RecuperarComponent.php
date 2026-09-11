<?php

namespace Iehaa\Usuarios\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Usuarios\Classes\CpanelAuth;
use Iehaa\Usuarios\Models\Usuario;
use Illuminate\Support\Facades\Mail;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

/**
 * "Olvidé mi contraseña": genera un enlace de un solo uso y lo envía por
 * correo. Nunca revela si un correo existe o no en el sistema (se responde
 * siempre el mismo mensaje genérico), para no facilitar enumeración de
 * cuentas.
 */
class RecuperarComponent extends ComponentBase
{
    const MINUTOS_VALIDEZ = 60;
    const SEGUNDOS_ENTRE_ENVIOS = 120;

    public function componentDetails()
    {
        return [
            'name'        => 'recuperarComponent',
            'description' => 'Solicitud de restablecimiento de contraseña'
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
    }

    public function onSolicitar()
    {
        $data = Input::all();

        $validator = Validator::make($data, [
            'email' => 'required|email',
        ], [
            'email.required' => '* Campo obligatorio.',
            'email.email' => 'Correo inválido.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $mensajeGenerico = 'Si el correo está registrado en el sistema, te enviamos un enlace para restablecer tu contraseña. Revisá tu bandeja de entrada.';

        $usuario = Usuario::where('email', trim($data['email']))->where('activo', true)->first();

        if ($usuario && $this->puedeEnviarNuevoEnlace($usuario)) {
            $this->enviarEnlace($usuario);
        }

        // Misma respuesta exista o no la cuenta: evita revelar qué correos
        // están registrados.
        return [
            'estado' => 'exito',
            'mensaje' => $mensajeGenerico,
        ];
    }

    private function puedeEnviarNuevoEnlace(Usuario $usuario): bool
    {
        if (!$usuario->reset_token_expira) {
            return true;
        }

        $creadoHace = $usuario->reset_token_expira->copy()->subMinutes(self::MINUTOS_VALIDEZ)->diffInSeconds(now());

        return $creadoHace >= self::SEGUNDOS_ENTRE_ENVIOS;
    }

    private function enviarEnlace(Usuario $usuario): void
    {
        $token = bin2hex(random_bytes(32));

        $usuario->reset_token = hash('sha256', $token);
        $usuario->reset_token_expira = now()->addMinutes(self::MINUTOS_VALIDEZ);
        $usuario->timestamps = false;
        $usuario->save();
        $usuario->timestamps = true;

        $enlace = url('/restablecer-password') . '?email=' . urlencode($usuario->email) . '&token=' . $token;

        try {
            Mail::send('iehaa.usuarios::mail.recuperar-password', [
                'nombre' => $usuario->nombre,
                'enlace' => $enlace,
                'minutosValidez' => self::MINUTOS_VALIDEZ,
            ], function ($message) use ($usuario) {
                $message->to($usuario->email, $usuario->nombre);
                $message->subject('Restablecé tu contraseña — IEHAA');
            });
        } catch (\Throwable $e) {
            \Log::error('[IEHAA] No se pudo enviar el correo de recuperación de contraseña: ' . $e->getMessage());
        }
    }
}
