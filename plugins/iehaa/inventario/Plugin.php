<?php

namespace Iehaa\Inventario;

use Backend\Facades\Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * inventario Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.inventario::lang.plugin.name',
            'description' => 'iehaa.inventario::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register(): void
    {

    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot(): void
    {

    }

    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {

        return [
            \Iehaa\Inventario\Components\InventarioComponent::class => 'inventarioComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'iehaa.inventario.some_permission' => [
                'tab' => 'iehaa.inventario::lang.plugin.name',
                'label' => 'iehaa.inventario::lang.permissions.some_permission',
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
            'inventario' => [
                'label'       => 'iehaa.inventario::lang.plugin.name',
                'url'         => Backend::url('iehaa/inventario/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['iehaa.inventario.*'],
                'order'       => 500,
            ],
        ];
    }
}
