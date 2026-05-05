<?php

namespace IEHAA\Documentos;

use Backend\Facades\Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * Documentos Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.documentos::lang.plugin.name',
            'description' => 'iehaa.documentos::lang.plugin.description',
            'author'      => 'IEHAA',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register(): void
    {
        $this->app->register(\Barryvdh\DomPDF\ServiceProvider::class);
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot(): void {}

    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {
        return [
            \IEHAA\Documentos\Components\DocumentoComponent::class => 'documentocomponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'iehaa.documentos.some_permission' => [
                'tab' => 'iehaa.documentos::lang.plugin.name',
                'label' => 'iehaa.documentos::lang.permissions.some_permission',
                'roles' => [UserRole::CODE_DEVELOPER, UserRole::CODE_PUBLISHER],
            ],
        ];
    }

    /**
     * Registers backend navigation items for this plugin.
     */
    public function registerNavigation(): array
    {
        return []; // Remove this line to activate

        return [
            'documentos' => [
                'label'       => 'iehaa.documentos::lang.plugin.name',
                'url'         => Backend::url('iehaa/documentos/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['iehaa.documentos.*'],
                'order'       => 500,
            ],
        ];
    }
}
