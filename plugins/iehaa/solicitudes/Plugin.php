<?php

namespace Iehaa\Solicitudes;

use System\Classes\PluginBase;

/**
 * solicitudes Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = [
        'Iehaa.Fabio',
        'Iehaa.Fondo',
        'Iehaa.Usuarios',
    ];

    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.solicitudes::lang.plugin.name',
            'description' => 'iehaa.solicitudes::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-bell'
        ];
    }

    public function register(): void {}

    public function boot(): void {}

    public function registerComponents(): array
    {
        return [
            \Iehaa\Solicitudes\Components\CedjagComponent::class => 'cedjagComponent',
            \Iehaa\Solicitudes\Components\SolicitudesComponent::class => 'solicitudesComponent',
        ];
    }
}
