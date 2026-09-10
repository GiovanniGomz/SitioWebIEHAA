<?php

namespace Iehaa\Tipopublicaciones\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Reportes\Classes\ReporteModulo;
use Iehaa\Tipopublicaciones\Models\TipoPublicaciones;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class TipoPublicacionComponent extends ComponentBase
{
    use ReporteModulo;

    public function componentDetails()
    {
        return [
            'name'        => 'TipoPublicacionComponent',
            'description' => 'Modulo de tipo de publicaciones'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['tipo_publicaciones'] = $this->obtenerTodos();
    }

    public function obtenerTodos()
    {
        return TipoPublicaciones::orderBy('nombre')->get();
    }

    public function onRegistrar()
    {
        $data = Input::all();
        $id = $data['id'] ?? null;

        $this->validaciones($data, $id);

        $tipo = $id ? TipoPublicaciones::find($id) : new TipoPublicaciones();

        if (!$tipo) {
            throw new ValidationException(['nombre' => 'El registro ya no existe.']);
        }

        $tipo->nombre = trim($data['nombre']);
        $tipo->save();

        return [
            '#listado' => $this->renderPartial('@listado', ['tipo_publicaciones' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => $id ? '¡Modificado correctamente!' : '¡Almacenado correctamente!'
        ];
    }

    public function onEliminar()
    {
        $tipo = TipoPublicaciones::find(intval(post('id')));

        if (!$tipo) {
            return [
                '#listado' => $this->renderPartial('@listado', ['tipo_publicaciones' => $this->obtenerTodos()]),
                'estado' => 'error',
                'mensaje' => 'El tipo de publicación ya no existe.'
            ];
        }

        $tipo->delete();

        return [
            '#listado' => $this->renderPartial('@listado', ['tipo_publicaciones' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function onGetTipoPublicacion()
    {
        return ['tipo_publicacion' => TipoPublicaciones::find(post('id'))];
    }

    public function validaciones($data, $id = null)
    {
        $validator = Validator::make($data, [
            'nombre' => ['required', 'string', 'min:3', 'max:80', 'regex:/^[\pL\s]+$/u'],
        ], [
            'nombre.required' => '* Campo obligatorio.',
            'nombre.min'      => 'Mínimo 3 caracteres.',
            'nombre.max'      => 'Máximo 80 caracteres.',
            'nombre.regex'    => 'Solo se permiten letras y espacios.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $duplicado = TipoPublicaciones::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(trim($data['nombre']))])
            ->when($id, fn ($q) => $q->where('id', '!=', $id))
            ->exists();

        if ($duplicado) {
            throw new ValidationException(['nombre' => 'Ya existe un tipo de publicación con ese nombre.']);
        }
    }

    protected function datosReporte(): array
    {
        $filas = [];
        foreach ($this->obtenerTodos() as $i => $t) {
            $filas[] = [$i + 1, $t->nombre];
        }

        return ['Listado de tipos de publicación', ['#', 'Nombre'], $filas, 'tipos_publicacion'];
    }
}
