<?php

namespace IEHAA\Documentos\Components;

use Cms\Classes\ComponentBase;
use IEHAA\Documentos\Models\Documento;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

use Illuminate\Support\Facades\Storage;

class DocumentoComponent extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'documentoComponent',
            'description' => 'Modulo de documentos'
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
        $this->page['documentos'] = Documento::all();
    }

    public function onRegistrar()
    {
        $data = Input::all();
        $archivo = Input::file('archivo');

        //Llave primaria
        $id = $data['id'];


        if ($id) { //Actualizando
            $this->validacionesModificar($data, $archivo);

            $documento = Documento::find($id);
            $documento->nombre = $data['nombre'];

            if ($archivo) {
                $documento->archivo = $this->guardarArchivo($archivo, $data['archivo_tmp']);
            } else {
                //Si no viene archivo es porque no se esta cambiando
                $documento->archivo = $data['archivo_tmp'];
            }

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $documento = new Documento();
            $documento->nombre = $data['nombre'];

            $this->validacionesRegistrar($data);

            $documento->archivo = $this->guardarArchivo($archivo);

            $mensaje = '¡Almacenado correctamente!';
        }

        $documento->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'documentos' => Documento::all()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    function onGetDocumento()
    {
        $id = post('id');
        $documento = Documento::find($id);

        return ['documento' => $documento];
    }

    function onEliminar()
    {
        $id = post('id');
        $documento = Documento::find($id);

        $this->eliminarArchivo($documento->archivo);

        $documento->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'documentos' => Documento::all()
            ]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function validacionesRegistrar($data)
    {
        $rules = [
            'nombre' => 'required|min:3',
            'archivo' => 'required'
        ];

        $customMessages = [
            'nombre.required' => '* Campo obligatorio.',
            'nombre.min'      => 'Minimo 3 caracteres',
            'archivo.required' => '* Campo obligatorio'
        ];

        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function validacionesModificar($data, $archivo)
    {
        if (!$archivo && !$data['archivo_tmp']) {

            $rules = [
                'nombre' => 'required|min:3',
                'archivo' => 'required'
            ];

            $customMessages = [
                'nombre.required' => '* Campo obligatorio.',
                'nombre.min'      => 'Minimo 3 caracteres',
                'archivo.required' => '* Campo obligatorio'
            ];
        } else {
            $rules = [
                'nombre' => 'required|min:3',
            ];

            $customMessages = [
                'nombre.required' => '* Campo obligatorio.',
                'nombre.min'      => 'Minimo 3 caracteres',
            ];
        }



        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function guardarArchivo($archivo, $nombreArchivo = false)
    {
        $uploadPath = 'storage/app/uploads/public/documentos/';

        if ($nombreArchivo) $this->eliminarArchivo($nombreArchivo);

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
        $archivo->move($uploadPath, $nombreArchivo);
        return $nombreArchivo;
    }

    public function eliminarArchivo($nombreArchivo)
    {
        Storage::delete('uploads/public/documentos/' . $nombreArchivo);
    }
}
