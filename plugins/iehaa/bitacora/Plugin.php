<?php

namespace Iehaa\Bitacora;

use Iehaa\Bitacora\Classes\RegistroAutomatico;
use System\Classes\PluginBase;

/**
 * Bitácora: registro de las acciones más importantes del sistema (quién hizo
 * qué y cuándo). Los alta/modificación/eliminación de los módulos se
 * registran solos escuchando los eventos de los modelos; las acciones
 * especiales (inicio de sesión, respaldos, descargas...) llaman a
 * Bitacora::registrar() de forma explícita.
 */
class Plugin extends PluginBase
{
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.bitacora::lang.plugin.name',
            'description' => 'iehaa.bitacora::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-history',
        ];
    }

    public function register(): void {}

    public function boot(): void
    {
        RegistroAutomatico::escuchar();
    }

    public function registerComponents(): array
    {
        return [
            \Iehaa\Bitacora\Components\BitacoraComponent::class => 'bitacoraComponent',
        ];
    }
}
