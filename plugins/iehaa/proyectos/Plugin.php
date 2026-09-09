<?php

namespace Iehaa\Proyectos;

use System\Classes\PluginBase;

/**
 * proyectos Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = ['Iehaa.Investigadores'];

    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.proyectos::lang.plugin.name',
            'description' => 'iehaa.proyectos::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-flask'
        ];
    }

    public function register(): void {}

    public function boot(): void {}

    public function registerComponents(): array
    {
        return [
            \Iehaa\Proyectos\Components\ProyectoComponent::class => 'proyectoComponent',
        ];
    }
}
