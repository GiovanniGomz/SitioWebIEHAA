<?php

namespace Iehaa\Investigadores\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Categoriainvestigadores\Models\CategoriaInvestigador;
use Iehaa\Facultades\Models\Facultad;
use Iehaa\Investigadores\Models\Investigador;
use Iehaa\Reportes\Classes\ReporteModulo;
use Iehaa\Tipoinvestigadores\Models\TipoInvestigador;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class InvestigadorComponent extends ComponentBase
{
    use ReporteModulo;

    public function componentDetails()
    {
        return [
            'name'        => 'investigadorComponent',
            'description' => 'Modulo de investigadores'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['investigadores'] = $this->obtenerTodos();
        $this->page['facultades'] = Facultad::orderBy('nombre')->get();
        $this->page['categorias'] = CategoriaInvestigador::orderBy('nombre')->get();
        $this->page['tipo_investigadores'] = TipoInvestigador::orderBy('nombre')->get();
    }

    public function obtenerTodos()
    {
        return Investigador::with(['facultad', 'tipo_investigador', 'categoria_investigador'])
            ->orderBy('apellido')
            ->get();
    }

    public function onRegistrar()
    {
        $data = Input::all();
        $id = $data['id'] ?? null;

        $data['carnet'] = strtoupper(trim($data['carnet'] ?? ''));
        $data['telefono'] = preg_replace('/\D+/', '', $data['telefono'] ?? '');

        $this->validaciones($data, $id);

        $investigador = $id ? Investigador::find($id) : new Investigador();

        if (!$investigador) {
            throw new ValidationException(['nombre' => 'El investigador ya no existe.']);
        }

        $investigador->nombre = trim($data['nombre']);
        $investigador->apellido = trim($data['apellido']);
        $investigador->carnet = $data['carnet'];
        $investigador->email = trim($data['email']);
        $investigador->telefono = $data['telefono'];
        $investigador->facultad_id = $data['facultad'];
        $investigador->categoria_investigador_id = $data['categoria_investigador'];
        $investigador->tipo_investigador_id = $data['tipo_investigador'];
        $investigador->sexo = $data['sexo'];
        $investigador->descripcion = trim($data['descripcion']);

        $investigador->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'investigadores' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => $id ? '¡Modificado correctamente!' : '¡Almacenado correctamente!'
        ];
    }

    public function onEliminar()
    {
        $investigador = Investigador::find(intval(post('id')));

        if (!$investigador) {
            return [
                '#listado' => $this->renderPartial('@listado', ['investigadores' => $this->obtenerTodos()]),
                'estado' => 'error',
                'mensaje' => 'El investigador ya no existe.'
            ];
        }

        $investigador->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'investigadores' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function onGetInvestigador()
    {
        return ['investigador' => Investigador::find(post('id'))];
    }

    public function validaciones($data, $id = null)
    {
        $validator = Validator::make($data, [
            'nombre'                 => ['required', 'string', 'max:60', 'regex:/^[\pL\s.\'\-]+$/u'],
            'apellido'               => ['required', 'string', 'max:60', 'regex:/^[\pL\s.\'\-]+$/u'],
            'carnet'                 => ['required', 'regex:/^[A-Z]{2}[0-9]{5}$/'],
            'telefono'               => ['required', 'regex:/^[2670][0-9]{7}$/'],
            'email'                  => ['required', 'email', 'max:120'],
            'facultad'               => ['required'],
            'categoria_investigador' => ['required'],
            'tipo_investigador'      => ['required'],
            'sexo'                   => ['required'],
            'descripcion'            => ['required', 'string', 'min:10'],
        ], [
            'nombre.required'      => '* Campo obligatorio.',
            'nombre.regex'         => 'El nombre solo admite letras y espacios.',
            'apellido.required'    => '* Campo obligatorio.',
            'apellido.regex'       => 'El apellido solo admite letras y espacios.',
            'carnet.required'      => '* Campo obligatorio.',
            'carnet.regex'         => 'Formato inválido. Debe ser 2 letras y 5 números, por ejemplo HS21002.',
            'telefono.required'    => '* Campo obligatorio.',
            'telefono.regex'       => 'Debe ser un número salvadoreño válido de 8 dígitos.',
            'email.required'       => '* Campo obligatorio.',
            'email.email'          => 'Ingresá un correo válido.',
            'facultad.required'    => '* Campo obligatorio.',
            'categoria_investigador.required' => '* Campo obligatorio.',
            'tipo_investigador.required'      => '* Campo obligatorio.',
            'sexo.required'        => '* Campo obligatorio.',
            'descripcion.required' => '* Campo obligatorio.',
            'descripcion.min'      => 'Escribí al menos 10 caracteres.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $emailDuplicado = Investigador::whereRaw('LOWER(TRIM(email)) = ?', [mb_strtolower(trim($data['email']))])
            ->when($id, fn ($q) => $q->where('id', '!=', $id))
            ->exists();

        if ($emailDuplicado) {
            throw new ValidationException(['email' => 'Este valor ya existe.']);
        }

        $telefonoDuplicado = Investigador::where('telefono', $data['telefono'])
            ->when($id, fn ($q) => $q->where('id', '!=', $id))
            ->exists();

        if ($telefonoDuplicado) {
            throw new ValidationException(['telefono' => 'Ya existe un investigador con ese teléfono.']);
        }

        $carnetDuplicado = Investigador::whereRaw('UPPER(carnet) = ?', [$data['carnet']])
            ->when($id, fn ($q) => $q->where('id', '!=', $id))
            ->exists();

        if ($carnetDuplicado) {
            throw new ValidationException(['carnet' => 'Este valor ya existe.']);
        }
    }

    protected function datosReporte(): array
    {
        $filas = [];
        foreach ($this->obtenerTodos() as $i => $inv) {
            $filas[] = [
                $i + 1,
                trim($inv->nombre . ' ' . $inv->apellido),
                strtoupper($inv->carnet),
                $inv->telefono,
                $inv->email,
                optional($inv->facultad)->nombre ?: '—',
                optional($inv->tipo_investigador)->nombre ?: '—',
                optional($inv->categoria_investigador)->nombre ?: '—',
            ];
        }

        return [
            'Listado de investigadores',
            ['#', 'Nombre completo', 'Carnet', 'Teléfono', 'Correo', 'Facultad', 'Tipo', 'Categoría'],
            $filas,
            'investigadores',
        ];
    }
}
