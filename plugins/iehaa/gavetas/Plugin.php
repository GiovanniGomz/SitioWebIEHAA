<?php

namespace Iehaa\Gavetas;

use Backend\Facades\Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * gavetas Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.gavetas::lang.plugin.name',
            'description' => 'iehaa.gavetas::lang.plugin.description',
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
            \IEHAA\Gavetas\Components\GavetaComponent::class => 'gavetaComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'iehaa.gavetas.some_permission' => [
                'tab' => 'iehaa.gavetas::lang.plugin.name',
                'label' => 'iehaa.gavetas::lang.permissions.some_permission',
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
            'gavetas' => [
                'label'       => 'iehaa.gavetas::lang.plugin.name',
                'url'         => Backend::url('iehaa/gavetas/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['iehaa.gavetas.*'],
                'order'       => 500,
            ],
        ];
    }
}
