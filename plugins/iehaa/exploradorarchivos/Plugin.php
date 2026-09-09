<?php

namespace Iehaa\Exploradorarchivos;

use System\Classes\PluginBase;

/**
 * exploradorarchivos Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = [
        'Iehaa.Archiveros',
        'Iehaa.Gavetas',
        'Iehaa.Carpetas',
        'Iehaa.Folders',
        'Iehaa.Fabio',
        'Iehaa.Estante',
        'Iehaa.Anaquel',
        'Iehaa.Coleccion',
        'Iehaa.Fondo',
    ];

    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'Explorador de archivos',
            'description' => 'Navegador unificado para Fabio Castillo y Fondo Bibliográfico',
            'author'      => 'iehaa',
            'icon'        => 'icon-folder-open'
        ];
    }

    public function register(): void {}

    public function boot(): void {}

    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {
        return [
            \Iehaa\Exploradorarchivos\Components\ExploradorComponent::class => 'exploradorComponent',
        ];
    }
}
