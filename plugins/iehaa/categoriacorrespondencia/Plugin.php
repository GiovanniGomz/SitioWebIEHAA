<?php

namespace Iehaa\Categoriacorrespondencia;

use System\Classes\PluginBase;

/**
 * Categorías de correspondencia (usadas por el módulo de Correspondencia).
 */
class Plugin extends PluginBase
{
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.categoriacorrespondencia::lang.plugin.name',
            'description' => 'iehaa.categoriacorrespondencia::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-tags',
        ];
    }

    public function register(): void {}

    public function boot(): void {}

    public function registerComponents(): array
    {
        return [
            \Iehaa\Categoriacorrespondencia\Components\CategoriaCorrespondenciaComponent::class => 'categoriaCorrespondenciaComponent',
        ];
    }
}
