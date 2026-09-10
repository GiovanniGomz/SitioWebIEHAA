<?php

namespace Iehaa\Reportes\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Documentos\Models\Documento;
use Iehaa\Fabio\Models\Fabio;
use Iehaa\Facultades\Models\Facultad;
use Iehaa\Fondo\Models\Fondo;
use Iehaa\Investigadores\Models\Investigador;
use Iehaa\Proyectos\Models\Proyecto;
use Iehaa\Publicaciones\Models\Publicacion;
use Iehaa\Tipopublicaciones\Models\TipoPublicaciones;
use Iehaa\Usuarios\Classes\CpanelAuth;

class ReporteComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'reporteComponent',
            'description' => 'Panel de estadísticas del instituto'
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

        $this->page['tarjetas'] = $this->tarjetasResumen();
        $this->page['graficaFacultades'] = json_encode($this->investigadoresPorFacultad());
        $this->page['graficaCategorias'] = json_encode($this->investigadoresPorCategoria());
        $this->page['graficaDocumentos'] = json_encode($this->documentosPorModulo());
        $this->page['graficaPublicaciones'] = json_encode($this->publicacionesPorTipo());
        $this->page['facultades'] = Facultad::all();
        $this->page['tiposPublicacion'] = TipoPublicaciones::all();
        $this->page['enlaces'] = $this->enlacesReportes();
        $this->page['investigadoresListado'] = Investigador::with('facultad')->get();
        $this->page['publicacionesListado'] = Publicacion::with(['tipo_publicacion', 'investigador'])->orderByDesc('id')->get();
    }

    private function tarjetasResumen(): array
    {
        return [
            ['label' => 'Investigadores', 'valor' => Investigador::count(), 'icono' => 'bi-person-fill', 'color' => 'primary'],
            ['label' => 'Proyectos', 'valor' => Proyecto::count(), 'icono' => 'bi-award-fill', 'color' => 'success'],
            ['label' => 'Publicaciones', 'valor' => Publicacion::count(), 'icono' => 'bi-journal-text', 'color' => 'warning'],
            ['label' => 'Documentos totales', 'valor' => Documento::count() + Fabio::count() + Fondo::count(), 'icono' => 'bi-file-earmark-fill', 'color' => 'info'],
        ];
    }

    private function investigadoresPorFacultad(): array
    {
        $conteo = Investigador::with('facultad')->get()
            ->groupBy(fn($i) => $i->facultad->nombre ?? 'Sin facultad')
            ->map->count();

        return ['labels' => $conteo->keys()->values()->all(), 'valores' => $conteo->values()->all()];
    }

    private function investigadoresPorCategoria(): array
    {
        $conteo = Investigador::with('categoria_investigador')->get()
            ->groupBy(fn($i) => $i->categoria_investigador->nombre ?? 'Sin categoría')
            ->map->count();

        return ['labels' => $conteo->keys()->values()->all(), 'valores' => $conteo->values()->all()];
    }

    private function documentosPorModulo(): array
    {
        return [
            'labels' => ['Descargas', 'Fabio Castillo', 'Fondo Bibliográfico'],
            'valores' => [Documento::count(), Fabio::count(), Fondo::count()],
        ];
    }

    private function publicacionesPorTipo(): array
    {
        $conteo = Publicacion::with('tipo_publicacion')->get()
            ->groupBy(fn($p) => $p->tipo_publicacion->nombre ?? 'Sin tipo')
            ->map->count();

        return ['labels' => $conteo->keys()->values()->all(), 'valores' => $conteo->values()->all()];
    }

    public function onFiltrarInvestigadores()
    {
        $facultadId = post('facultad_id');

        $query = Investigador::with('facultad');

        if ($facultadId) {
            $query->where('facultad_id', $facultadId);
        }

        return [
            '#reporte-tabla-investigadores' => $this->renderPartial('@tabla_investigadores', [
                'investigadores' => $query->get(),
            ]),
        ];
    }

    public function onFiltrarPublicaciones()
    {
        $tipoId = post('tipo_publicacion_id');

        $query = Publicacion::with(['tipo_publicacion', 'investigador']);

        if ($tipoId) {
            $query->where('tipo_publicacion_id', $tipoId);
        }

        return [
            '#reporte-tabla-publicaciones' => $this->renderPartial('@tabla_publicaciones', [
                'publicaciones' => $query->orderByDesc('id')->get(),
            ]),
        ];
    }

    private function enlacesReportes(): array
    {
        return [
            'Investigación' => [
                ['label' => 'Investigadores', 'pdf' => '/reportePDFInvestigador', 'excel' => '/reporteExcelInvestigador'],
                ['label' => 'Proyectos', 'pdf' => '/reportePDFProyectos', 'excel' => '/reporteExcelProyectos'],
                ['label' => 'Publicaciones', 'pdf' => '/reportePDFPublicaciones', 'excel' => '/reporteExcelPublicaciones'],
                ['label' => 'Facultades', 'pdf' => '/reportePDFFacultades', 'excel' => '/reporteExcelFacultades'],
                ['label' => 'Tipo de Investigador', 'pdf' => '/reportePDFTipoInvestigadores', 'excel' => '/reporteExcelTipoInvestigadores'],
                ['label' => 'Categoría de Investigador', 'pdf' => '/reportePDFCategoriaInvestigadores', 'excel' => '/reporteExcelCategoriaInvestigadores'],
                ['label' => 'Tipo de Publicación', 'pdf' => '/reportePDFTipoPublicaciones', 'excel' => '/reporteExcelTipoPublicaciones'],
                ['label' => 'Descargas públicas', 'pdf' => '/reportePDFDescarga', 'excel' => '/reporteExcelDescarga'],
                ['label' => 'Gestión documental', 'pdf' => '/reportePDFExpedientes', 'excel' => '/reporteExcelExpedientes'],
            ],
            'Fabio Castillo' => [
                ['label' => 'Archiveros', 'pdf' => '/reportePDFArchivero', 'excel' => '/reporteExcelArchivero'],
                ['label' => 'Gavetas', 'pdf' => '/reportePDFGaveta', 'excel' => '/reporteExcelGaveta'],
                ['label' => 'Carpetas', 'pdf' => '/reportePDFCarpeta', 'excel' => '/reporteExcelCarpeta'],
                ['label' => 'Folders', 'pdf' => '/reportePDFFolder', 'excel' => '/reporteExcelFolder'],
                ['label' => 'Documentos', 'pdf' => '/reportePDFFabio', 'excel' => '/reporteExcelFabio'],
            ],
            'Fondo Bibliográfico' => [
                ['label' => 'Estantes', 'pdf' => '/reportePDFEstante', 'excel' => '/reporteExcelEstante'],
                ['label' => 'Anaqueles', 'pdf' => '/reportePDFAnaquel', 'excel' => '/reporteExcelAnaquel'],
                ['label' => 'Colecciones', 'pdf' => '/reportePDFColeccion', 'excel' => '/reporteExcelColeccion'],
                ['label' => 'Documentos', 'pdf' => '/reportePDFFondo', 'excel' => '/reporteExcelFondo'],
            ],
        ];
    }
}
