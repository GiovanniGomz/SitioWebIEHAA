<?php

namespace Iehaa\Carpetas;

use Backend\Facades\Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * carpetas Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.carpetas::lang.plugin.name',
            'description' => 'iehaa.carpetas::lang.plugin.description',
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
            \IEHAA\Carpetas\Components\CarpetaComponent::class => 'carpetaComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'iehaa.carpetas.some_permission' => [
                'tab' => 'iehaa.carpetas::lang.plugin.name',
                'label' => 'iehaa.carpetas::lang.permissions.some_permission',
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
            'carpetas' => [
                'label'       => 'iehaa.carpetas::lang.plugin.name',
                'url'         => Backend::url('iehaa/carpetas/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['iehaa.carpetas.*'],
                'order'       => 500,
            ],
        ];
    }
}
