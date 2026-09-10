<?php

namespace Iehaa\Facultades\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Facultades\Models\Facultad;
use Iehaa\Reportes\Classes\ReporteModulo;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class FacultadComponent extends ComponentBase
{
    use ReporteModulo;

    public function componentDetails()
    {
        return [
            'name'        => 'facultadComponent',
            'description' => 'Modulo de facultades'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['facultades'] = $this->obtenerTodos();
    }

    public function obtenerTodos()
    {
        return Facultad::orderBy('nombre')->get();
    }

    public function onRegistrar()
    {
        $data = Input::all();
        $id = $data['id'] ?? null;

        $this->validaciones($data, $id);

        $facultad = $id ? Facultad::find($id) : new Facultad();

        if (!$facultad) {
            throw new ValidationException(['nombre' => 'El registro ya no existe.']);
        }

        $facultad->nombre = trim($data['nombre']);
        $facultad->save();

        return [
            '#listado' => $this->renderPartial('@listado', ['facultades' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => $id ? '¡Modificado correctamente!' : '¡Almacenado correctamente!'
        ];
    }

    public function onEliminar()
    {
        $facultad = Facultad::find(intval(post('id')));

        if (!$facultad) {
            return [
                '#listado' => $this->renderPartial('@listado', ['facultades' => $this->obtenerTodos()]),
                'estado' => 'error',
                'mensaje' => 'La facultad ya no existe.'
            ];
        }

        $facultad->delete();

        return [
            '#listado' => $this->renderPartial('@listado', ['facultades' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function onGetfacultad()
    {
        return ['facultad' => Facultad::find(post('id'))];
    }

    public function validaciones($data, $id = null)
    {
        $validator = Validator::make($data, [
            'nombre' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[\pL\pN\s.\-]+$/u'],
        ], [
            'nombre.required' => '* Campo obligatorio.',
            'nombre.min'      => 'Mínimo 3 caracteres.',
            'nombre.max'      => 'Máximo 100 caracteres.',
            'nombre.regex'    => 'Formato no valido',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $duplicado = Facultad::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(trim($data['nombre']))])
            ->when($id, fn($q) => $q->where('id', '!=', $id))
            ->exists();

        if ($duplicado) {
            throw new ValidationException(['nombre' => 'Este valor ya existe.']);
        }
    }

    protected function datosReporte(): array
    {
        $filas = [];
        foreach ($this->obtenerTodos() as $i => $f) {
            $filas[] = [$i + 1, $f->nombre];
        }

        return ['Listado de facultades', ['#', 'Nombre'], $filas, 'facultades'];
    }
}
