<?php

namespace Iehaa\Reportes;

use System\Classes\PluginBase;

/**
 * reportes Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = [
        'Iehaa.Investigadores',
        'Iehaa.Facultades',
        'Iehaa.Categoriainvestigadores',
        'Iehaa.Documentos',
        'Iehaa.Fabio',
        'Iehaa.Fondo',
        'Iehaa.Publicaciones',
        'Iehaa.Tipopublicaciones',
        'Iehaa.Proyectos',
        'Iehaa.Usuarios',
    ];

    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.reportes::lang.plugin.name',
            'description' => 'iehaa.reportes::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-bar-chart'
        ];
    }

    public function register(): void {}

    public function boot(): void {}

    public function registerComponents(): array
    {
        return [
            \Iehaa\Reportes\Components\ReporteComponent::class => 'reporteComponent',
            \Iehaa\Reportes\Components\DashboardComponent::class => 'dashboardComponent',
        ];
    }
}
