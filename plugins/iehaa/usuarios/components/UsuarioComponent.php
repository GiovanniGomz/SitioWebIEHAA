<?php

namespace Iehaa\Usuarios\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Usuarios\Classes\CpanelAuth;
use Iehaa\Usuarios\Models\Usuario;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class UsuarioComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'usuarioComponent',
            'description' => 'Modulo de usuarios del panel'
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

        $this->page['usuarios'] = Usuario::all();
        $this->page['usuarioActual'] = CpanelAuth::usuario();
    }

    public function onRegistrar()
    {
        if (!CpanelAuth::esAdmin()) {
            return redirect('/dashboard');
        }

        $data = Input::all();
        $id = $data['id'] ?? null;

        if ($id) { // Actualizando
            $this->validaciones($data, true);

            $usuario = Usuario::find($id);
            $usuario->nombre = $data['nombre'];
            $usuario->email = $data['email'];
            $usuario->rol = $data['rol'];
            $usuario->activo = isset($data['activo']) ? true : false;

            if (!empty($data['password'])) {
                $usuario->password = $data['password'];
            }

            $mensaje = '¡Modificado correctamente!';
        } else { // Creando
            $this->validaciones($data, false);

            $usuario = new Usuario();
            $usuario->nombre = $data['nombre'];
            $usuario->email = $data['email'];
            $usuario->password = $data['password'];
            $usuario->rol = $data['rol'];
            $usuario->activo = isset($data['activo']) ? true : false;

            $mensaje = '¡Almacenado correctamente!';
        }

        $usuario->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'usuarios' => Usuario::all(),
                'usuarioActual' => CpanelAuth::usuario(),
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    public function onGetUsuario()
    {
        $id = post('id');
        $usuario = Usuario::find($id);

        return ['usuario' => $usuario];
    }

    public function onEliminar()
    {
        if (!CpanelAuth::esAdmin()) {
            return redirect('/dashboard');
        }

        $id = intval(post('id'));
        $actual = CpanelAuth::usuario();

        if ($actual && $actual->id === $id) {
            return [
                'estado' => 'error',
                'mensaje' => 'No podés eliminar tu propio usuario mientras estás conectado.'
            ];
        }

        Usuario::find($id)?->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'usuarios' => Usuario::all(),
                'usuarioActual' => CpanelAuth::usuario(),
            ]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function validaciones($data, $esModificar)
    {
        $rules = [
            'nombre' => 'required|min:3',
            'email' => 'required|email',
            'rol' => 'required|in:admin,editor',
        ];

        if (!$esModificar) {
            $rules['password'] = 'required|min:6';
        } elseif (!empty($data['password'])) {
            $rules['password'] = 'min:6';
        }

        $customMessages = [
            'nombre.required' => '* Campo obligatorio.',
            'email.required' => '* Campo obligatorio.',
            'email.email' => 'Correo inválido.',
            'rol.required' => '* Campo obligatorio.',
            'password.required' => '* Campo obligatorio.',
            'password.min' => 'Mínimo 6 caracteres.',
        ];

        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
