<?php

namespace Iehaa\Archiveros\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Archiveros\Models\Archivero;
use Iehaa\Reportes\Classes\ReporteModulo;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class ArchiveroComponent extends ComponentBase
{
    use ReporteModulo;

    public function componentDetails()
    {
        return [
            'name'        => 'archiveroComponent',
            'description' => 'Modulo de archiveros'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['archiveros'] = Archivero::all();
    }

    public function onRegistrar()
    {
        $data = Input::all();
        $id = $data['id'] ?? null;

        if ($id) {
            $this->validaciones($data);

            $archivero = Archivero::find($id);
            if (!$archivero) {
                throw new ValidationException(['codigo' => 'El registro ya no existe.']);
            }
            $archivero->codigo = $data['codigo'];
            $mensaje = '¡Modificado correctamente!';
        } else {
            $archivero = new Archivero();
            $archivero->codigo = $data['codigo'];
            $archivero->url = $this->generarURL();

            $this->validaciones($data);

            $mensaje = '¡Almacenado correctamente!';
        }

        $archivero->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'archiveros' => Archivero::all()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    public function onGetArchivero()
    {
        return ['archivero' => Archivero::find(post('id'))];
    }

    public function onEliminar()
    {
        $archivero = Archivero::find(intval(post('id')));

        if (!$archivero) {
            return [
                '#listado' => $this->renderPartial('@listado', ['archiveros' => Archivero::all()]),
                'estado' => 'error',
                'mensaje' => 'El archivero ya no existe.'
            ];
        }

        $archivero->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'archiveros' => Archivero::all()
            ]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function generarURL()
    {
        return password_hash(md5(uniqid()), PASSWORD_BCRYPT);
    }

    public function validaciones($data)
    {
        $validator = Validator::make($data, [
            'codigo' => 'required',
        ], [
            'codigo.required' => '* Campo obligatorio.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    protected function datosReporte(): array
    {
        $filas = [];
        foreach (Archivero::orderBy('codigo')->get() as $i => $a) {
            $filas[] = [$i + 1, 'Archivero ' . $a->codigo];
        }

        return ['Listado de archiveros — Fabio Castillo', ['#', 'Archivero'], $filas, 'archiveros'];
    }
}
