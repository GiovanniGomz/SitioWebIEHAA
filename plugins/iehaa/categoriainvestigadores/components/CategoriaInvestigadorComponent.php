<?php

namespace Iehaa\Categoriainvestigadores\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Categoriainvestigadores\Models\CategoriaInvestigador;
use Iehaa\Reportes\Classes\ReporteModulo;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class CategoriaInvestigadorComponent extends ComponentBase
{
    use ReporteModulo;

    public function componentDetails()
    {
        return [
            'name'        => 'categoriaInvestigadorComponent',
            'description' => 'Modulo de categoria de Investigadores'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['categoria_investigadores'] = $this->obtenerTodos();
    }

    public function obtenerTodos()
    {
        return CategoriaInvestigador::orderBy('nombre')->get();
    }

    public function onRegistrar()
    {
        $data = Input::all();
        $id = $data['id'] ?? null;

        $this->validaciones($data, $id);

        $categoria = $id ? CategoriaInvestigador::find($id) : new CategoriaInvestigador();

        if (!$categoria) {
            throw new ValidationException(['nombre' => 'El registro ya no existe.']);
        }

        $categoria->nombre = trim($data['nombre']);
        $categoria->save();

        return [
            '#listado' => $this->renderPartial('@listado', ['categoria_investigadores' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => $id ? '¡Modificado correctamente!' : '¡Almacenado correctamente!'
        ];
    }

    public function onEliminar()
    {
        $categoria = CategoriaInvestigador::find(intval(post('id')));

        if (!$categoria) {
            return [
                '#listado' => $this->renderPartial('@listado', ['categoria_investigadores' => $this->obtenerTodos()]),
                'estado' => 'error',
                'mensaje' => 'La categoría ya no existe.'
            ];
        }

        $categoria->delete();

        return [
            '#listado' => $this->renderPartial('@listado', ['categoria_investigadores' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function onGetCategoriaInvestigador()
    {
        return ['categoria_investigador' => CategoriaInvestigador::find(post('id'))];
    }

    public function validaciones($data, $id = null)
    {
        $validator = Validator::make($data, [
            'nombre' => ['required', 'string', 'min:3', 'max:80', 'regex:/^[\pL\s]+$/u'],
        ], [
            'nombre.required' => '* Campo obligatorio.',
            'nombre.min'      => 'Mínimo 3 caracteres.',
            'nombre.max'      => 'Máximo 80 caracteres.',
            'nombre.regex'    => 'Formato no valido.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $duplicado = CategoriaInvestigador::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(trim($data['nombre']))])
            ->when($id, fn($q) => $q->where('id', '!=', $id))
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

        return ['Listado de categorías de investigador', ['#', 'Nombre'], $filas, 'categorias_investigador'];
    }
}
