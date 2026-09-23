<?php

namespace Iehaa\Usuarios\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Envía un correo de prueba con la configuración SMTP actual, para verificar
 * que quedó bien puesta antes de confiar en ella para recuperar contraseña
 * o el envío de documentos de Préstamo CEDJAG.
 */
class ProbarCorreo extends Command
{
    protected $name = 'iehaa:probar-correo';

    protected $description = 'Envía un correo de prueba para verificar la configuración SMTP.';

    protected $signature = 'iehaa:probar-correo {destino : Correo al que se enviará la prueba}';

    public function handle()
    {
        $destino = $this->argument('destino');

        $this->info('Mailer configurado: ' . config('mail.default'));
        $this->info('Host: ' . config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port'));
        $this->info('Remitente: ' . config('mail.from.address'));
        $this->info('Enviando a ' . $destino . '...');

        try {
            Mail::raw(
                "Este es un correo de prueba del Sitio Web IEHAA.\n\n" .
                "Si lo recibiste, la configuración SMTP quedó funcionando correctamente:\n" .
                "el recuperar contraseña y el envío de documentos de Préstamo CEDJAG ya pueden enviar correos.",
                function ($message) use ($destino) {
                    $message->to($destino);
                    $message->subject('Prueba de correo — Sitio Web IEHAA');
                }
            );
        } catch (\Throwable $e) {
            $this->error('No se pudo enviar: ' . $e->getMessage());
            return 1;
        }

        $this->info('¡Enviado correctamente! Revisá la bandeja de entrada (y la de spam) de ' . $destino . '.');
        return 0;
    }
}
