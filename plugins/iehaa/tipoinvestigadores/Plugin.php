<?php

namespace Iehaa\Tipoinvestigadores;

use Backend\Facades\Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * tipoinvestigadores Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.tipoinvestigadores::lang.plugin.name',
            'description' => 'iehaa.tipoinvestigadores::lang.plugin.description',
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
            \IEHAA\Tipoinvestigadores\Components\TipoInvestigadorComponent::class => 'tipoInvestigadorComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'iehaa.tipoinvestigadores.some_permission' => [
                'tab' => 'iehaa.tipoinvestigadores::lang.plugin.name',
                'label' => 'iehaa.tipoinvestigadores::lang.permissions.some_permission',
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
            'tipoinvestigadores' => [
                'label'       => 'iehaa.tipoinvestigadores::lang.plugin.name',
                'url'         => Backend::url('iehaa/tipoinvestigadores/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['iehaa.tipoinvestigadores.*'],
                'order'       => 500,
            ],
        ];
    }
}
