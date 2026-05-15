<?php

namespace Iehaa\Archiveros;

use Backend\Facades\Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * archiveros Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.archiveros::lang.plugin.name',
            'description' => 'iehaa.archiveros::lang.plugin.description',
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
            \IEHAA\Archiveros\Components\ArchiveroComponent::class => 'archiveroComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'iehaa.archiveros.some_permission' => [
                'tab' => 'iehaa.archiveros::lang.plugin.name',
                'label' => 'iehaa.archiveros::lang.permissions.some_permission',
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
            'archiveros' => [
                'label'       => 'iehaa.archiveros::lang.plugin.name',
                'url'         => Backend::url('iehaa/archiveros/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['iehaa.archiveros.*'],
                'order'       => 500,
            ],
        ];
    }
}
