<?php

namespace Iehaa\Tipopublicaciones;

use Backend\Facades\Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * tipopublicaciones Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.tipopublicaciones::lang.plugin.name',
            'description' => 'iehaa.tipopublicaciones::lang.plugin.description',
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
            \IEHAA\Tipopublicaciones\Components\TipoPublicacionComponent::class => 'TipoPublicacionComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'iehaa.tipopublicaciones.some_permission' => [
                'tab' => 'iehaa.tipopublicaciones::lang.plugin.name',
                'label' => 'iehaa.tipopublicaciones::lang.permissions.some_permission',
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
            'tipopublicaciones' => [
                'label'       => 'iehaa.tipopublicaciones::lang.plugin.name',
                'url'         => Backend::url('iehaa/tipopublicaciones/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['iehaa.tipopublicaciones.*'],
                'order'       => 500,
            ],
        ];
    }
}
