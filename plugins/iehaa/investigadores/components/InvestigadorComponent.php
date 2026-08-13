<?php

namespace Iehaa\Investigadores\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Investigadores\Models\Investigador;
use IEHAA\Documentos\Models\Documento;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

class InvestigadorComponent extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'investigadorComponent',
            'description' => 'Modulo de investigadores'
        ];
    }

    public function onRegistrar()
    {
        $data = Input::all();

        //Llave primaria
        $id = $data['id'];


        if ($id) { //Actualizando
            $this->validaciones($data);

            $investigador = Investigador::find($id);
            $investigador->nombre = $data['nombre'];
            $investigador->apellido = $data['apellido'];
            $investigador->email = $data['email'];
            $investigador->carnet = $data['carnet'];
            $investigador->telefono = $data['telefono'];
            $investigador->profesion = $data['profesion'];
            $investigador->grado = $data['grado'];

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $investigador = new Investigador();
            $investigador->nombre = $data['nombre'];
            $investigador->apellido = $data['apellido'];
            $investigador->email = $data['email'];
            $investigador->carnet = $data['carnet'];
            $investigador->telefono = $data['telefono'];
            $investigador->profesion = $data['profesion'];
            $investigador->grado = $data['grado'];

            $this->validaciones($data);

            $mensaje = '¡Almacenado correctamente!';
        }

        $investigador->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'investigadores' => Investigador::all()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    public function validaciones($data)
    {
        $rules = [
            'nombre' => 'required|max:30',
            'apellido' => 'required|max:30',
            'telefono' => 'required|max:8',
            'email' => 'required|max:60',
            'carnet' => 'required|max:7',
            'profesion' => 'required|max:60',
            'grado' => 'required|max:30'
        ];

        $customMessages = [
            'nombre.required' => '* Campo obligatorio.',
            'nombre.max'      => 'Maximo 30 caracteres',
            'apellido.required' => '* Campo obligatorio.',
            'apellido.max'      => 'Maximo 30 caracteres',
            'telefono.required' => '* Campo obligatorio.',
            'telefono.max'      => 'Maximo 8 caracteres',
            'email.required' => '* Campo obligatorio.',
            'email.max'      => 'Maximo 60 caracteres',
            'carnet.required' => '* Campo obligatorio.',
            'carnet.max'      => 'Maximo 7 caracteres',
            'profesion.required' => '* Campo obligatorio.',
            'profesion.max'      => 'Maximo 60 caracteres',
            'grado.required' => '* Campo obligatorio.',
            'grado.max'      => 'Maximo 30 caracteres'
        ];

        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Returns the properties provided by the component
     */
    public function defineProperties()
    {
        return [];
    }
}
