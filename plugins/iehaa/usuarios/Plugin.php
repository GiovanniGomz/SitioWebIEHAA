<?php

namespace Iehaa\Usuarios;

use Backend\Facades\Backend;
use Backend\Models\UserRole;
use Illuminate\Support\Facades\Route;
use System\Classes\PluginBase;

/**
 * usuarios Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.usuarios::lang.plugin.name',
            'description' => 'iehaa.usuarios::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-user'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register(): void {}

    /**
     * Boot method, called right before the request route.
     */
    public function boot(): void
    {
        Route::aliasMiddleware('cpanel.auth', \Iehaa\Usuarios\Classes\CpanelAuthMiddleware::class);
    }

    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {
        return [
            \Iehaa\Usuarios\Components\LoginComponent::class => 'loginComponent',
            \Iehaa\Usuarios\Components\UsuarioComponent::class => 'usuarioComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return [];
    }

    /**
     * Registers backend navigation items for this plugin.
     */
    public function registerNavigation(): array
    {
        return [];
    }
}
