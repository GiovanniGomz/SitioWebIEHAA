<?php

namespace Iehaa\Fabio;

use Backend\Facades\Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * fabio Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.fabio::lang.plugin.name',
            'description' => 'iehaa.fabio::lang.plugin.description',
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
            \IEHAA\Fabio\Components\FabioComponent::class => 'fabioComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'iehaa.fabio.some_permission' => [
                'tab' => 'iehaa.fabio::lang.plugin.name',
                'label' => 'iehaa.fabio::lang.permissions.some_permission',
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
            'fabio' => [
                'label'       => 'iehaa.fabio::lang.plugin.name',
                'url'         => Backend::url('iehaa/fabio/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['iehaa.fabio.*'],
                'order'       => 500,
            ],
        ];
    }
}
