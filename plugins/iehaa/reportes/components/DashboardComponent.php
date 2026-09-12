<?php

namespace Iehaa\Reportes\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Documentos\Models\Documento;
use Iehaa\Fabio\Models\Fabio;
use Iehaa\Fondo\Models\Fondo;
use Iehaa\Investigadores\Models\Investigador;
use Iehaa\Proyectos\Models\Proyecto;
use Iehaa\Publicaciones\Models\Publicacion;
use Iehaa\Usuarios\Classes\CpanelAuth;
use Iehaa\Usuarios\Models\Usuario;

class DashboardComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'dashboardComponent',
            'description' => 'Panel principal con datos reales del sistema'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        if (!CpanelAuth::check()) {
            return redirect('/login');
        }

        $this->page['tarjetas'] = [
            ['label' => 'Usuarios del panel', 'valor' => Usuario::count(), 'icono' => 'bi-people-fill', 'color' => 'primary', 'sub' => 'Con acceso al sistema'],
            ['label' => 'Investigadores', 'valor' => Investigador::count(), 'icono' => 'bi-person-badge-fill', 'color' => 'success', 'sub' => 'Registrados en total'],
            ['label' => 'Documentos totales', 'valor' => Documento::count() + Fabio::count() + Fondo::count(), 'icono' => 'bi-file-earmark-text-fill', 'color' => 'warning', 'sub' => 'Entre fondos y descargas'],
            ['label' => 'Publicaciones', 'valor' => Publicacion::count(), 'icono' => 'bi-journal-text', 'color' => 'info', 'sub' => 'Producción académica'],
        ];

        $this->page['proyectosRecientes'] = Proyecto::with('investigador')->orderByDesc('id')->limit(5)->get();
        $this->page['publicacionesRecientes'] = Publicacion::with('investigador')->orderByDesc('id')->limit(5)->get();

        $fabioTotal = Fabio::count();
        $fondoTotal = Fondo::count();
        $descargasTotal = Documento::count();

        $this->page['graficoFondos'] = [
            'labels' => ['Fabio Castillo', 'Fondo Bibliográfico', 'Descargas públicas'],
            'valores' => [$fabioTotal, $fondoTotal, $descargasTotal],
        ];

        $this->page['fechaHoy'] = now()->format('d/m/Y');
        $this->page['usuarioActual'] = CpanelAuth::usuario();
    }
}
