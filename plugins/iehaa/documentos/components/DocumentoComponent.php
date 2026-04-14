<?php

namespace IEHAA\Documentos\Components;

use Cms\Classes\ComponentBase;
use IEHAA\Documentos\Models\Documento;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

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

    public function onCreate()
    {
        $data = Input::all();
        $archivo = Input::file('archivo');

        if (!$archivo) {
            $data['archivo'] = '';
        }

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

        $documento = new Documento();
        $documento->nombre = $data['nombre'];

        if ($archivo) {
            $uploadPath = 'uploads/documentos/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $archivo->move($uploadPath, $nombreArchivo);
            $documento->archivo = $nombreArchivo;
        }

        $documento->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'documentos' => Documento::all()
            ]),
            'status' => 'success'
        ];
    }
}
