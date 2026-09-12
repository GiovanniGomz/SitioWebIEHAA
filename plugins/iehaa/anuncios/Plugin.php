<?php

namespace Iehaa\Anuncios;

use System\Classes\PluginBase;

/**
 * Anuncios: secciones opcionales que el administrador puede publicar en la
 * página principal (título, texto e imagen o video). Si no hay ninguno
 * activo, la página pública se ve exactamente igual que siempre.
 */
class Plugin extends PluginBase
{
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.anuncios::lang.plugin.name',
            'description' => 'iehaa.anuncios::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-bullhorn',
        ];
    }

    public function register(): void {}

    public function boot(): void {}

    public function registerComponents(): array
    {
        return [
            \Iehaa\Anuncios\Components\AnuncioComponent::class => 'anuncioComponent',
        ];
    }
}
