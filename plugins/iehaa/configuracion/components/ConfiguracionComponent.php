<?php

namespace Iehaa\Configuracion\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Configuracion\Models\Configuracion;
use Iehaa\Usuarios\Classes\CpanelAuth;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class ConfiguracionComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'configuracionComponent',
            'description' => 'Configuración general del sitio'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        if (!CpanelAuth::esAdmin()) {
            return redirect('/dashboard');
        }

        $this->page['configuracion'] = Configuracion::actual();
    }

    public function onGuardar()
    {
        if (!CpanelAuth::esAdmin()) {
            return redirect('/dashboard');
        }

        $data = Input::all();

        $validator = Validator::make($data, [
            'nombre_sitio' => 'required|min:3',
            'email_contacto' => 'nullable|email',
        ], [
            'nombre_sitio.required' => '* Campo obligatorio.',
            'email_contacto.email' => 'Correo inválido.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $configuracion = Configuracion::actual();
        $configuracion->nombre_sitio = $data['nombre_sitio'];
        $configuracion->descripcion_sitio = $data['descripcion_sitio'] ?? '';
        $configuracion->email_contacto = $data['email_contacto'] ?? '';
        $configuracion->telefono_contacto = $data['telefono_contacto'] ?? '';
        $configuracion->direccion = $data['direccion'] ?? '';
        $configuracion->save();

        return [
            'estado' => 'exito',
            'mensaje' => '¡Configuración guardada correctamente!',
        ];
    }
}
