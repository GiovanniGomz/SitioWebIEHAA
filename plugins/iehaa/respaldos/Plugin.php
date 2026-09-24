<?php

namespace Iehaa\Respaldos;

use Iehaa\Respaldos\Classes\ServicioRespaldo;
use Iehaa\Usuarios\Classes\CpanelAuth;
use Illuminate\Console\Scheduling\Schedule;
use System\Classes\PluginBase;

/**
 * Respaldos de la base de datos: automáticos (cada cierto tiempo) y manuales
 * ("Respaldar ahora" desde el panel, con descarga a cualquier lugar de la PC).
 */
class Plugin extends PluginBase
{
    public function pluginDetails(): array
    {
        return [
            'name'        => 'iehaa.respaldos::lang.plugin.name',
            'description' => 'iehaa.respaldos::lang.plugin.description',
            'author'      => 'iehaa',
            'icon'        => 'icon-database',
        ];
    }

    public function register(): void
    {
        $this->registerConsoleCommand('iehaa.respaldar', \Iehaa\Respaldos\Console\Respaldar::class);
        $this->registerConsoleCommand('iehaa.restaurar', \Iehaa\Respaldos\Console\Restaurar::class);
        $this->registerConsoleCommand('iehaa.limpiar', \Iehaa\Respaldos\Console\Limpiar::class);
    }

    public function boot(): void
    {
        // Red de seguridad: si la PC estaba apagada a la hora programada, el
        // primer usuario que entre al panel dispara el respaldo pendiente.
        \Event::listen('cms.page.beforeDisplay', function () {
            if (!app()->runningInConsole() && CpanelAuth::check()) {
                ServicioRespaldo::dispararSiCorresponde();
            }
        });
    }

    /** Tarea programada (php artisan schedule:run): respaldo diario de madrugada. */
    public function registerSchedule($schedule): void
    {
        $schedule->command('iehaa:respaldar --auto')->dailyAt('02:00');
    }

    public function registerComponents(): array
    {
        return [
            \Iehaa\Respaldos\Components\RespaldoComponent::class => 'respaldoComponent',
        ];
    }
}
