<?php

namespace Iehaa\Registrocorrespondencia;

use System\Classes\PluginBase;

/**
 * Correspondencia: registro de los documentos que el instituto envía y
 * recibe (distinto del módulo "Préstamo CEDJAG", que gestiona las
 * solicitudes de préstamo de documentos del acervo).
 */
class Plugin extends PluginBase
{
    public $require = ['Iehaa.Facultades', 'Iehaa.Categoriacorrespondencia'];

    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.registrocorrespondencia::lang.plugin.name',
            'description' => 'iehaa.registrocorrespondencia::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-send',
        ];
    }

    public function register(): void {}

    public function boot(): void {}

    public function registerComponents(): array
    {
        return [
            \Iehaa\Registrocorrespondencia\Components\RegistroCorrespondenciaComponent::class => 'registroCorrespondenciaComponent',
        ];
    }
}
