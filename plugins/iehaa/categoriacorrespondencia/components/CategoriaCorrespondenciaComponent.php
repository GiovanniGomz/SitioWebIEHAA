<?php

namespace Iehaa\Categoriacorrespondencia\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Categoriacorrespondencia\Models\CategoriaCorrespondencia;
use Iehaa\Reportes\Classes\ReporteModulo;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class CategoriaCorrespondenciaComponent extends ComponentBase
{
    use ReporteModulo;

    public function componentDetails()
    {
        return [
            'name'        => 'categoriaCorrespondenciaComponent',
            'description' => 'Categorías de correspondencia'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['categorias'] = $this->obtenerTodos();
    }

    public function obtenerTodos()
    {
        return CategoriaCorrespondencia::orderBy('nombre')->get();
    }

    public function onRegistrar()
    {
        $data = Input::all();
        $id = $data['id'] ?? null;

        $this->validaciones($data, $id);

        $categoria = $id ? CategoriaCorrespondencia::find($id) : new CategoriaCorrespondencia();

        if (!$categoria) {
            throw new ValidationException(['nombre' => 'El registro ya no existe.']);
        }

        $categoria->nombre = trim($data['nombre']);
        $categoria->save();

        return [
            '#listado' => $this->renderPartial('@listado', ['categorias' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => $id ? '¡Modificado correctamente!' : '¡Almacenado correctamente!'
        ];
    }

    public function onEliminar()
    {
        $categoria = CategoriaCorrespondencia::find(intval(post('id')));

        if (!$categoria) {
            return [
                '#listado' => $this->renderPartial('@listado', ['categorias' => $this->obtenerTodos()]),
                'estado' => 'error',
                'mensaje' => 'La categoría ya no existe.'
            ];
        }

        $categoria->delete();

        return [
            '#listado' => $this->renderPartial('@listado', ['categorias' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function onGetCategoriaCorrespondencia()
    {
        return ['categoria' => CategoriaCorrespondencia::find(post('id'))];
    }

    public function validaciones($data, $id = null)
    {
        $validator = Validator::make($data, [
            'nombre' => ['required', 'string', 'min:2', 'max:120'],
        ], [
            'nombre.required' => '* Campo obligatorio.',
            'nombre.min'      => 'Mínimo 2 caracteres.',
            'nombre.max'      => 'Máximo 120 caracteres.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $duplicado = CategoriaCorrespondencia::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(trim($data['nombre']))])
            ->when($id, fn ($q) => $q->where('id', '!=', $id))
            ->exists();

        if ($duplicado) {
            throw new ValidationException(['nombre' => 'Este valor ya existe.']);
        }
    }

    protected function datosReporte(): array
    {
        $filas = [];
        foreach ($this->obtenerTodos() as $i => $c) {
            $filas[] = [$i + 1, $c->nombre];
        }

        return ['Listado de categorías de correspondencia', ['#', 'Nombre'], $filas, 'categorias_correspondencia'];
    }
}
