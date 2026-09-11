<?php

namespace Iehaa\Mensajescontacto;

use System\Classes\PluginBase;

/**
 * Mensajes enviados desde el formulario de contacto de la página pública.
 */
class Plugin extends PluginBase
{
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.mensajescontacto::lang.plugin.name',
            'description' => 'iehaa.mensajescontacto::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-envelope',
        ];
    }

    public function register(): void {}

    public function boot(): void {}

    public function registerComponents(): array
    {
        return [
            \Iehaa\Mensajescontacto\Components\MensajeContactoComponent::class => 'mensajeContactoComponent',
        ];
    }
}
