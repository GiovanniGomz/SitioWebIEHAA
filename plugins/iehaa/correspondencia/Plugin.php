<?php

namespace Iehaa\Correspondencia;

use System\Classes\PluginBase;

/**
 * Correspondencia — gestiona las solicitudes de préstamo enviadas desde
 * CEDJAG (aceptar/rechazar) y el historial de correspondencia entregada.
 */
class Plugin extends PluginBase
{
    /**
     * @var array Dependencias del plugin
     */
    public $require = ['Iehaa.Solicitudes', 'Iehaa.Categoriacorrespondencia'];

    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.correspondencia::lang.plugin.name',
            'description' => 'iehaa.correspondencia::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-envelope',
        ];
    }

    public function register(): void {}

    public function boot(): void {}

    public function registerComponents(): array
    {
        return [
            \Iehaa\Correspondencia\Components\CorrespondenciaComponent::class => 'correspondenciaComponent',
        ];
    }
}
