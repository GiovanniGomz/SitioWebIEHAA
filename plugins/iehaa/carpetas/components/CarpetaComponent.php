<?php

namespace Iehaa\Carpetas\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Carpetas\Models\Carpeta;
use Iehaa\Gavetas\Models\Gaveta;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

class CarpetaComponent extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'carpetaComponent',
            'description' => 'Modulo de carpetas'
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
        $this->page['carpetas'] = $this->obtenerCarpetas();
    }

    public function obtenerCarpetas()
    {
        $url = get('id');

        return Carpeta::whereHas('Gaveta', function ($query) use ($url) {
            $query->where('url', $url);
        })->get();
    }

    public function onRegistrar()
    {

        $data = Input::all();

        //Llave primaria
        $id = $data['id'];


        if ($id) { //Actualizando
            $this->validaciones($data);

            $carpeta = Carpeta::find($id);
            $carpeta->codigo = $data['codigo'];

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $url = get('id');
            $gaveta = Gaveta::where('url', $url)->first();

            if (!$gaveta) {
                exit;
            }

            $carpeta = new Carpeta();
            $carpeta->codigo = $data['codigo'];
            $carpeta->url = $this->generarURL();
            $carpeta->gaveta_id = $gaveta->id;

            \Log::info("Datos de carpeta");
            \Log::info($carpeta);

            $this->validaciones($data);

            $mensaje = '¡Almacenado correctamente!';
        }

        $carpeta->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'carpetas' => $this->obtenerCarpetas()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    function onGetCarpeta()
    {
        $id = post('id');
        $carpeta = Carpeta::find($id);

        return ['carpeta' => $carpeta];
    }

    function onEliminar()
    {
        \Log::info("Acceso a onEliminar");

        $id = post('id');
        $id = intval($id);

        $carpeta = Carpeta::find($id);

        $carpeta->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'carpetas' => $this->obtenerCarpetas()
            ]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function generarURL()
    {
        $url = md5(uniqid());
        $urlHash = $this->hash($url);

        return $urlHash;
    }

    public function hash($valor)
    {
        $hash = password_hash($valor, PASSWORD_BCRYPT);
        return $hash;
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
