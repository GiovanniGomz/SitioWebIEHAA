<?php

namespace Iehaa\Anaquel;

use Backend\Facades\Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * anaquel Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.anaquel::lang.plugin.name',
            'description' => 'iehaa.anaquel::lang.plugin.description',
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
            \IEHAA\Anaquel\Components\AnaquelComponent::class => 'anaquelComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'iehaa.anaquel.some_permission' => [
                'tab' => 'iehaa.anaquel::lang.plugin.name',
                'label' => 'iehaa.anaquel::lang.permissions.some_permission',
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
            'anaquel' => [
                'label'       => 'iehaa.anaquel::lang.plugin.name',
                'url'         => Backend::url('iehaa/anaquel/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['iehaa.anaquel.*'],
                'order'       => 500,
            ],
        ];
    }
}
