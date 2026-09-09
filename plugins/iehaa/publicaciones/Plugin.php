<?php

namespace Iehaa\Publicaciones;

use System\Classes\PluginBase;

/**
 * publicaciones Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = ['Iehaa.Investigadores', 'Iehaa.Tipopublicaciones'];

    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.publicaciones::lang.plugin.name',
            'description' => 'iehaa.publicaciones::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-newspaper-o'
        ];
    }

    public function register(): void {}

    public function boot(): void {}

    public function registerComponents(): array
    {
        return [
            \Iehaa\Publicaciones\Components\PublicacionComponent::class => 'publicacionComponent',
        ];
    }
}
