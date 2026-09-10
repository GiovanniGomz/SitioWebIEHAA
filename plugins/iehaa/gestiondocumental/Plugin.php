<?php

namespace Iehaa\Gestiondocumental;

use System\Classes\PluginBase;

/**
 * Gestión documental — inventario de expedientes del archivo.
 */
class Plugin extends PluginBase
{
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.gestiondocumental::lang.plugin.name',
            'description' => 'iehaa.gestiondocumental::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-folder-open',
        ];
    }

    public function register(): void {}

    public function boot(): void {}

    public function registerComponents(): array
    {
        return [
            \Iehaa\Gestiondocumental\Components\ExpedienteComponent::class => 'expedienteComponent',
        ];
    }
}
