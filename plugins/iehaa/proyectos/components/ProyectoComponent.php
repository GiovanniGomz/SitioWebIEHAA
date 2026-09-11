<?php

namespace Iehaa\Proyectos\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Investigadores\Models\Investigador;
use Iehaa\Proyectos\Models\Proyecto;
use Iehaa\Reportes\Classes\ReporteModulo;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class ProyectoComponent extends ComponentBase
{
    use ReporteModulo;

    public function componentDetails()
    {
        return [
            'name'        => 'proyectoComponent',
            'description' => 'Modulo de proyectos de investigación'
        ];
    }

    public function defineProperties()
    {
        return [
            'limite' => [
                'title' => 'Límite de registros',
                'description' => 'Usado en la página pública para mostrar solo los más recientes',
                'default' => '',
                'type' => 'string',
            ],
        ];
    }

    public function onRun()
    {
        $this->page['proyectos'] = $this->obtenerTodos();
        $this->page['investigadores'] = Investigador::all();
    }

    public function obtenerTodos()
    {
        $query = Proyecto::with('investigador')->orderByDesc('id');

        if ($limite = $this->property('limite')) {
            $query->limit((int) $limite);
        }

        return $query->get();
    }

    public function onRegistrar()
    {
        $data = Input::all();
        $id = $data['id'] ?? null;

        $this->validaciones($data);

        if ($id) {
            $proyecto = Proyecto::find($id);
            $mensaje = '¡Modificado correctamente!';
        } else {
            $proyecto = new Proyecto();
            $mensaje = '¡Almacenado correctamente!';
        }

        $proyecto->titulo = $data['titulo'];
        $proyecto->descripcion = $data['descripcion'];
        $proyecto->detalle = $data['detalle'] ?? '';
        $proyecto->investigador_id = $data['investigador_id'];
        $proyecto->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'proyectos' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    public function onGetProyecto()
    {
        $proyecto = Proyecto::find(post('id'));

        return ['proyecto' => $proyecto];
    }

    public function onEliminar()
    {
        Proyecto::find(intval(post('id')))?->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'proyectos' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function validaciones($data)
    {
        $validator = Validator::make($data, [
            'titulo' => 'required|min:3',
            'descripcion' => 'required',
            'investigador_id' => 'required',
        ], [
            'titulo.required' => '* Campo obligatorio.',
            'titulo.min' => 'Mínimo 3 caracteres.',
            'descripcion.required' => '* Campo obligatorio.',
            'investigador_id.required' => '* Campo obligatorio.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    protected function datosReporte(): array
    {
        $filas = [];
        $proyectos = Proyecto::with('investigador')->orderByDesc('id')->get();

        foreach ($proyectos as $i => $p) {
            $filas[] = [
                $i + 1,
                $p->titulo,
                $p->investigador ? trim($p->investigador->nombre . ' ' . $p->investigador->apellido) : '—',
                \Str::limit((string) $p->descripcion, 120),
            ];
        }

        return ['Listado de proyectos de investigación', ['#', 'Título', 'Investigador', 'Descripción'], $filas, 'proyectos'];
    }
}
