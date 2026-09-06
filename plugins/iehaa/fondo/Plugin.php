<?php

namespace Iehaa\Fondo;

use Backend\Facades\Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * fondo Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.fondo::lang.plugin.name',
            'description' => 'iehaa.fondo::lang.plugin.description',
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
            \IEHAA\Fondo\Components\FondoComponent::class => 'fondoComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'iehaa.fondo.some_permission' => [
                'tab' => 'iehaa.fondo::lang.plugin.name',
                'label' => 'iehaa.fondo::lang.permissions.some_permission',
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
            'fondo' => [
                'label'       => 'iehaa.fondo::lang.plugin.name',
                'url'         => Backend::url('iehaa/fondo/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['iehaa.fondo.*'],
                'order'       => 500,
            ],
        ];
    }
}
