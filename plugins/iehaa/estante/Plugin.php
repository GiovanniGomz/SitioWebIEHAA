<?php

namespace Iehaa\Estante;

use Backend\Facades\Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * estante Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.estante::lang.plugin.name',
            'description' => 'iehaa.estante::lang.plugin.description',
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
            \IEHAA\Estante\Components\EstanteComponent::class => 'estanteComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'iehaa.estante.some_permission' => [
                'tab' => 'iehaa.estante::lang.plugin.name',
                'label' => 'iehaa.estante::lang.permissions.some_permission',
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
            'estante' => [
                'label'       => 'iehaa.estante::lang.plugin.name',
                'url'         => Backend::url('iehaa/estante/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['iehaa.estante.*'],
                'order'       => 500,
            ],
        ];
    }
}
