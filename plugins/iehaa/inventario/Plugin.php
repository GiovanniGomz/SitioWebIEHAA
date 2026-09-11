<?php

namespace Iehaa\Inventario;

use System\Classes\PluginBase;

/**
 * Inventario de Activo Fijo.
 */
class Plugin extends PluginBase
{
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.inventario::lang.plugin.name',
            'description' => 'iehaa.inventario::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-archive',
        ];
    }

    public function register(): void {}

    public function boot(): void {}

    public function registerComponents(): array
    {
        return [
            \Iehaa\Inventario\Components\ActivoFijoComponent::class => 'activoFijoComponent',
        ];
    }
}
