<?php

namespace Iehaa\Investigadores;

use Backend\Facades\Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * investigadores Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.investigadores::lang.plugin.name',
            'description' => 'iehaa.investigadores::lang.plugin.description',
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
            \IEHAA\Investigadores\Components\InvestigadorComponent::class => 'investigadorComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'iehaa.investigadores.some_permission' => [
                'tab' => 'iehaa.investigadores::lang.plugin.name',
                'label' => 'iehaa.investigadores::lang.permissions.some_permission',
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
            'investigadores' => [
                'label'       => 'iehaa.investigadores::lang.plugin.name',
                'url'         => Backend::url('iehaa/investigadores/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['iehaa.investigadores.*'],
                'order'       => 500,
            ],
        ];
    }
}
