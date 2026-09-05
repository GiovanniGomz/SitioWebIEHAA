<?php

namespace Iehaa\Folders;

use Backend\Facades\Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * folders Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.folders::lang.plugin.name',
            'description' => 'iehaa.folders::lang.plugin.description',
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
            \IEHAA\Folders\Components\FolderComponent::class => 'folderComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'iehaa.folders.some_permission' => [
                'tab' => 'iehaa.folders::lang.plugin.name',
                'label' => 'iehaa.folders::lang.permissions.some_permission',
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
            'folders' => [
                'label'       => 'iehaa.folders::lang.plugin.name',
                'url'         => Backend::url('iehaa/folders/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['iehaa.folders.*'],
                'order'       => 500,
            ],
        ];
    }
}
