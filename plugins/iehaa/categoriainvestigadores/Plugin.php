<?php

namespace Iehaa\Categoriainvestigadores;

use Backend\Facades\Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * categoriainvestigadores Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.categoriainvestigadores::lang.plugin.name',
            'description' => 'iehaa.categoriainvestigadores::lang.plugin.description',
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
            \IEHAA\Categoriainvestigadores\Components\CategoriaInvestigadorComponent::class => 'categoriaInvestigadorComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'iehaa.categoriainvestigadores.some_permission' => [
                'tab' => 'iehaa.categoriainvestigadores::lang.plugin.name',
                'label' => 'iehaa.categoriainvestigadores::lang.permissions.some_permission',
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
            'categoriainvestigadores' => [
                'label'       => 'iehaa.categoriainvestigadores::lang.plugin.name',
                'url'         => Backend::url('iehaa/categoriainvestigadores/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['iehaa.categoriainvestigadores.*'],
                'order'       => 500,
            ],
        ];
    }
}
