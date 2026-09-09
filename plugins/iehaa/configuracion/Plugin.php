<?php

namespace Iehaa\Configuracion;

use System\Classes\PluginBase;

/**
 * configuracion Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = ['Iehaa.Usuarios'];

    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.configuracion::lang.plugin.name',
            'description' => 'iehaa.configuracion::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-cog'
        ];
    }

    public function register(): void {}

    public function boot(): void {}

    public function registerComponents(): array
    {
        return [
            \Iehaa\Configuracion\Components\ConfiguracionComponent::class => 'configuracionComponent',
        ];
    }
}
