<?php

namespace Iehaa\Tipopublicaciones\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Tipopublicaciones\Models\TipoPublicaciones;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

use Barryvdh\DomPDF\Facade\Pdf;

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TipoPublicacionComponent extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'TipoPublicacionComponent',
            'description' => 'Modulo de tipo de publicaciones'
        ];
    }

    public function onRun()
    {
        $this->page['tipo_publicaciones'] = $this->obtenerTodos();
    }

    public function obtenerTodos()
    {
        $tipo_publicaciones = TipoPublicaciones::all();

        return $tipo_publicaciones;
    }

    public function onRegistrar()
    {
        \Log::info("Acceso a funcion onRegistrar");

        $data = Input::all();

        //Llave primaria
        $id = $data['id'];


        if ($id) { //Actualizando
            $this->validaciones($data);

            $tipo_publicaciones = TipoPublicaciones::find($id);
            $tipo_publicaciones->nombre = $data['nombre'];

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $tipo_publicaciones = new TipoPublicaciones();
            $tipo_publicaciones->nombre = $data['nombre'];

            $this->validaciones($data);

            $mensaje = '¡Almacenado correctamente!';
        }

        \Log::info("Paso validaciones");

        $tipo_publicaciones->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'tipo_publicaciones' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    function onEliminar()
    {
        $id = post('id');
        $id = intval($id);
        $tipo_publicaciones = TipoPublicaciones::find($id);

        $tipo_publicaciones->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'tipo_publicaciones' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function validaciones($data)
    {
        $rules = [
            'nombre' => 'required|min:3',
        ];

        $customMessages = [
            'nombre.required' => '* Campo obligatorio.',
            'nombre.max'      => 'Minimo 3 caracteres'
        ];

        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    function onGetTipoPublicacion()
    {
        $id = post('id');
        $tipo_publicaciones = TipoPublicaciones::find($id);

        return ['tipo_publicacion' => $tipo_publicaciones];
    }

    public function generarPDF()
    {
        $tipoPublicaciones = $this->obtenerTodos();

        \Log::info(json_encode($tipoPublicaciones));

        $data = [
            'fecha' => now(),
            'tipos' => $tipoPublicaciones,
            'titulo' => 'Listado de tipos de publicaciones'
        ];

        $pdf = Pdf::loadView('iehaa.tipopublicaciones::reporte', $data);

        return $pdf->download('reporte_tipo_publicaciones.pdf');
    }


    /**
     * Returns the properties provided by the component
     */
    public function defineProperties()
    {
        return [];
    }
}
