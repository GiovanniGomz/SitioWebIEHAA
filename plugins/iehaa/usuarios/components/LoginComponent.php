<?php

namespace Iehaa\Usuarios\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Usuarios\Classes\CpanelAuth;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class LoginComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'loginComponent',
            'description' => 'Login del panel administrativo'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        if (CpanelAuth::check()) {
            return redirect('/dashboard');
        }
    }

    public function onLogin()
    {
        $data = Input::all();

        $validator = Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => '* Campo obligatorio.',
            'email.email' => 'Correo inválido.',
            'password.required' => '* Campo obligatorio.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $usuario = CpanelAuth::attempt($data['email'], $data['password']);

        if (!$usuario) {
            throw new ValidationException([
                'email' => 'Correo o contraseña incorrectos.'
            ]);
        }

        return redirect('/dashboard');
    }
}
