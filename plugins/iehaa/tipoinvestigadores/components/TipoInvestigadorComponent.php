<?php

namespace Iehaa\Tipoinvestigadores\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Reportes\Classes\ReporteModulo;
use Iehaa\Tipoinvestigadores\Models\TipoInvestigador;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class TipoInvestigadorComponent extends ComponentBase
{
    use ReporteModulo;

    public function componentDetails()
    {
        return [
            'name'        => 'tipoInvestigadorComponent',
            'description' => 'Modulo de tipo de investigadores'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['tipo_investigadores'] = $this->obtenerTodos();
    }

    public function obtenerTodos()
    {
        return TipoInvestigador::orderBy('nombre')->get();
    }

    public function onRegistrar()
    {
        $data = Input::all();
        $id = $data['id'] ?? null;

        $this->validaciones($data, $id);

        $tipo = $id ? TipoInvestigador::find($id) : new TipoInvestigador();

        if (!$tipo) {
            throw new ValidationException(['nombre' => 'El registro ya no existe.']);
        }

        $tipo->nombre = trim($data['nombre']);
        $tipo->save();

        return [
            '#listado' => $this->renderPartial('@listado', ['tipo_investigadores' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => $id ? '¡Modificado correctamente!' : '¡Almacenado correctamente!'
        ];
    }

    public function onEliminar()
    {
        $tipo = TipoInvestigador::find(intval(post('id')));

        if (!$tipo) {
            return [
                '#listado' => $this->renderPartial('@listado', ['tipo_investigadores' => $this->obtenerTodos()]),
                'estado' => 'error',
                'mensaje' => 'El tipo de investigador ya no existe.'
            ];
        }

        $tipo->delete();

        return [
            '#listado' => $this->renderPartial('@listado', ['tipo_investigadores' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function onGetTipoInvestigador()
    {
        return ['tipo_investigador' => TipoInvestigador::find(post('id'))];
    }

    public function validaciones($data, $id = null)
    {
        $validator = Validator::make($data, [
            'nombre' => ['required', 'string', 'min:3', 'max:80', 'regex:/^[\pL\s]+$/u'],
        ], [
            'nombre.required' => '* Campo obligatorio.',
            'nombre.min'      => 'Mínimo 3 caracteres.',
            'nombre.max'      => 'Máximo 80 caracteres.',
            'nombre.regex'    => 'Formato no válido.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $duplicado = TipoInvestigador::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(trim($data['nombre']))])
            ->when($id, fn($q) => $q->where('id', '!=', $id))
            ->exists();

        if ($duplicado) {
            throw new ValidationException(['nombre' => 'Este valor ya existe.']);
        }
    }

    protected function datosReporte(): array
    {
        $filas = [];
        foreach ($this->obtenerTodos() as $i => $t) {
            $filas[] = [$i + 1, $t->nombre];
        }

        return ['Listado de tipos de investigador', ['#', 'Nombre'], $filas, 'tipos_investigador'];
    }
}
