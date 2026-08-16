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

    public function onRun()
    {


        $this->page['investigadores'] = $this->obtenerTodos();
    }

    public function obtenerTodos()
    {
        $investigadores = Investigador::all();

        foreach ($investigadores as $investigador) {
            $investigador->facultad = $this->obtenerFacultad($investigador->facultad);
        }

        return $investigadores;
    }

    public function obtenerFacultad(int $valor)
    {
        switch ($valor) {
            case 1:
                return "Facultad de Agronomía";
                break;
            case 2:
                return "Facultad de Ciencias Económicas";
                break;
            case 3:
                return "Facultad de Ciencias y Humanidades";
                break;
            case 4:
                return "Facultad de Ciencias Naturales y Matemática";
                break;
            case 5:
                return "Facultad de Ingeniería y Arquitectura";
                break;
            case 6:
                return "Facultad de Jurisprudencia y Ciencias Sociales";
                break;
            case 7:
                return "Facultad de Medicina";
                break;
            case 8:
                return "Facultad de Odontología";
                break;
            case 9:
                return "Facultad de Química y Farmacia";
                break;
            case 10:
                return "Facultad Multidisciplinaria de Occidente";
                break;
            case 11:
                return "Facultad Multidisciplinaria de Oriente";
                break;
            case 12:
                return "Facultad Multidisciplinaria Paracentral";
                break;
            default:
                return "Error al obtener facultad";
        }
    }

    public function onRegistrar()
    {
        \Log::info("Acceso a funcion onRegistrar");

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
            $investigador->facultad = $data['facultad'] ?? '';
            $investigador->grado = $data['grado'] ?? '';

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $investigador = new Investigador();
            $investigador->nombre = $data['nombre'];
            $investigador->apellido = $data['apellido'];
            $investigador->email = $data['email'];
            $investigador->carnet = $data['carnet'];
            $investigador->telefono = $data['telefono'];
            $investigador->facultad = $data['facultad'] ?? '';
            $investigador->grado = $data['grado'] ?? '';

            $this->validaciones($data);

            $mensaje = '¡Almacenado correctamente!';
        }

        \Log::info("Paso validaciones");

        $investigador->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'investigadores' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    function onEliminar()
    {
        $id = post('id');
        $id = intval($id);
        $investigador = Investigador::find($id);

        $investigador->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'investigadores' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
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
            'facultad' => 'required',
            'grado' => 'required'
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
            'facultad.required' => '* Campo obligatorio.',
            'grado.required' => '* Campo obligatorio.'
        ];

        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    function onGetInvestigador()
    {
        $id = post('id');
        $investigador = Investigador::find($id);

        return ['investigador' => $investigador];
    }

    /**
     * Returns the properties provided by the component
     */
    public function defineProperties()
    {
        return [];
    }
}
