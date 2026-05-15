<?php

namespace Iehaa\Archiveros\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Archiveros\Models\Archivero;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

class ArchiveroComponent extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'archiveroComponent',
            'description' => 'Modulo de archiveros'
        ];
    }

    /**
     * Returns the properties provided by the component
     */
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

        //Llave primaria
        $id = $data['id'];


        if ($id) { //Actualizando
            $this->validaciones($data);

            $archivero = Archivero::find($id);
            $archivero->codigo = $data['codigo'];

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $archivero = new Archivero();
            $archivero->codigo = $data['codigo'];

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

    function onGetArchivero()
    {
        $id = post('id');
        $archivero = Archivero::find($id);

        return ['archivero' => $archivero];
    }

    function onEliminar()
    {
        \Log::info("Acceso a onEliminar");

        $id = post('id');
        $id = intval($id);

        $archivero = Archivero::find($id);

        $archivero->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'archiveros' => Archivero::all()
            ]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function validaciones($data)
    {
        $rules = [
            'codigo' => 'required|min:3',
        ];

        $customMessages = [
            'codigo.required' => '* Campo obligatorio.',
            'codigo.min'      => 'Minimo 3 caracteres'
        ];

        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
