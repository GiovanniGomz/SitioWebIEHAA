<?php

namespace Iehaa\Bitacora\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Bitacora\Classes\RegistroAutomatico;
use Iehaa\Bitacora\Models\Bitacora;
use Iehaa\Reportes\Classes\ReporteModulo;
use Iehaa\Usuarios\Classes\CpanelAuth;
use Winter\Storm\Support\Facades\Input;

class BitacoraComponent extends ComponentBase
{
    use ReporteModulo;

    /** Máximo de filas que se cargan en pantalla (la más reciente primero). */
    const LIMITE_PANTALLA = 2000;

    public function componentDetails()
    {
        return [
            'name'        => 'bitacoraComponent',
            'description' => 'Bitácora de acciones del sistema'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        if (!CpanelAuth::esAdmin()) {
            return redirect('/dashboard');
        }

        $this->page['registros'] = $this->consultar([])->limit(self::LIMITE_PANTALLA)->get();
        $this->page['modulos'] = $this->modulosDisponibles();
        $this->page['acciones'] = Bitacora::ACCIONES;
        $this->page['totalRegistros'] = Bitacora::count();
    }

    public function onFiltrar()
    {
        if (!CpanelAuth::esAdmin()) {
            return redirect('/dashboard');
        }

        $filtros = Input::only(['modulo', 'accion', 'desde', 'hasta']);

        return [
            '#listado' => $this->renderPartial('@listado', [
                'registros' => $this->consultar($filtros)->limit(self::LIMITE_PANTALLA)->get(),
            ]),
            'estado' => 'exito',
        ];
    }

    /** Módulos que ya aparecen en la bitácora + los que se pueden registrar. */
    protected function modulosDisponibles(): array
    {
        $registrados = Bitacora::query()->distinct()->pluck('modulo')->all();
        $conocidos = array_column(array_values(RegistroAutomatico::MODELOS), 0);

        $todos = array_values(array_unique(array_merge($registrados, $conocidos, ['Sesión', 'Respaldos'])));
        sort($todos, SORT_LOCALE_STRING);

        return $todos;
    }

    protected function consultar(array $filtros)
    {
        $q = Bitacora::query()->orderByDesc('created_at')->orderByDesc('id');

        if (!empty($filtros['modulo'])) {
            $q->where('modulo', $filtros['modulo']);
        }

        if (!empty($filtros['accion'])) {
            $q->where('accion', $filtros['accion']);
        }

        if (!empty($filtros['desde'])) {
            $q->where('created_at', '>=', $filtros['desde'] . ' 00:00:00');
        }

        if (!empty($filtros['hasta'])) {
            $q->where('created_at', '<=', $filtros['hasta'] . ' 23:59:59');
        }

        return $q;
    }

    protected function datosReporte(): array
    {
        $filtros = request()->only(['modulo', 'accion', 'desde', 'hasta']);
        $filas = [];

        foreach ($this->consultar($filtros)->limit(5000)->get() as $i => $r) {
            $filas[] = [
                $i + 1,
                $r->created_at ? $r->created_at->format('d/m/Y H:i:s') : '—',
                $r->usuario_nombre ?: 'Sistema / visitante',
                $r->accion_etiqueta,
                $r->modulo,
                $r->descripcion,
            ];
        }

        return [
            'Bitácora del sistema',
            ['#', 'Fecha y hora', 'Usuario', 'Acción', 'Módulo', 'Detalle'],
            $filas,
            'bitacora',
        ];
    }
}
