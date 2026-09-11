<?php

namespace Iehaa\Usuarios;

use Illuminate\Support\Facades\Route;
use System\Classes\PluginBase;
use Winter\Storm\Exception\AjaxException;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Exception\ValidationException;

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

        $this->registrarManejoDeErrores();
    }

    /**
     * Convierte cualquier error no controlado de un handler AJAX del panel en
     * un mensaje claro para el usuario, en lugar de una pantalla de error 500
     * que "rompe" el sistema. Los errores de validación y los mensajes de
     * aplicación se dejan pasar tal cual para que el formulario los muestre.
     *
     * Se registra como custom handler (no como listener de
     * 'exception.beforeRender') para que se ejecute ANTES del manejador de
     * errores del módulo System, que en modo debug devuelve el detalle técnico.
     */
    protected function registrarManejoDeErrores(): void
    {
        $handler = $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class);

        if (!method_exists($handler, 'error')) {
            return;
        }

        $handler->error(function (\Throwable $throwable, $code = 500, $fromConsole = false) {
            if ($fromConsole) {
                return null;
            }

            $request = request();

            $esAjax = $request->ajax()
                || $request->headers->has('X-WINTER-REQUEST-HANDLER')
                || strtolower((string) $request->headers->get('X-Requested-With')) === 'xmlhttprequest';

            if (!$esAjax) {
                return null;
            }

            if (
                $throwable instanceof ValidationException ||
                $throwable instanceof ApplicationException ||
                $throwable instanceof AjaxException ||
                $throwable instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
            ) {
                return null;
            }

            \Log::error('[IEHAA] Error no controlado en handler AJAX: ' . $throwable->getMessage(), [
                'url'     => $request->fullUrl(),
                'archivo' => $throwable->getFile() . ':' . $throwable->getLine(),
            ]);

            $msg = strtolower($throwable->getMessage());

            if (str_contains($msg, 'duplicate') || str_contains($msg, 'unique') || str_contains($msg, 'llave duplicada')) {
                return 'Este valor ya existe.';
            }

            if (
                str_contains($msg, 'foreign key') ||
                str_contains($msg, 'still referenced') ||
                str_contains($msg, 'llave foránea') ||
                str_contains($msg, 'violates foreign key')
            ) {
                return 'No se puede completar la acción porque este registro está siendo utilizado por otra información del sistema.';
            }

            return 'No se pudo completar la acción. Revisá los datos e intentá nuevamente. Si el problema continúa, contactá al administrador.';
        });
    }

    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {
        return [
            \Iehaa\Usuarios\Components\LoginComponent::class => 'loginComponent',
            \Iehaa\Usuarios\Components\UsuarioComponent::class => 'usuarioComponent',
            \Iehaa\Usuarios\Components\RecuperarComponent::class => 'recuperarComponent',
            \Iehaa\Usuarios\Components\RestablecerComponent::class => 'restablecerComponent',
            \Iehaa\Usuarios\Components\PerfilComponent::class => 'perfilComponent',
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
