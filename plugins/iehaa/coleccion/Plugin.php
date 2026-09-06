<?php

namespace Iehaa\Coleccion;

use Backend\Facades\Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * coleccion Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.coleccion::lang.plugin.name',
            'description' => 'iehaa.coleccion::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register(): void {}

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
            \IEHAA\Coleccion\Components\ColeccionComponent::class => 'coleccionComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'iehaa.coleccion.some_permission' => [
                'tab' => 'iehaa.coleccion::lang.plugin.name',
                'label' => 'iehaa.coleccion::lang.permissions.some_permission',
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
            'coleccion' => [
                'label'       => 'iehaa.coleccion::lang.plugin.name',
                'url'         => Backend::url('iehaa/coleccion/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['iehaa.coleccion.*'],
                'order'       => 500,
            ],
        ];
    }
}
