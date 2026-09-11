<?php

namespace Iehaa\Usuarios\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Usuarios\Classes\CpanelAuth;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

/**
 * Perfil del usuario conectado: cambio de contraseña y foto de perfil.
 * A diferencia de UsuarioComponent (administración de cuentas), este solo
 * permite a cada quien modificar su propia cuenta.
 */
class PerfilComponent extends ComponentBase
{
    protected $rutaSubida = 'storage/app/uploads/public/perfiles/';

    public function componentDetails()
    {
        return [
            'name'        => 'perfilComponent',
            'description' => 'Perfil del usuario conectado'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        if (!CpanelAuth::check()) {
            return redirect('/login');
        }

        $this->page['usuario'] = CpanelAuth::usuario();
    }

    public function onCambiarPassword()
    {
        $usuario = CpanelAuth::usuario();

        if (!$usuario) {
            return redirect('/login');
        }

        $data = Input::all();

        $validator = Validator::make($data, [
            'password_actual' => ['required'],
            'password_nueva' => ['required', 'min:8', 'confirmed', 'different:password_actual'],
        ], [
            'password_actual.required' => '* Campo obligatorio.',
            'password_nueva.required' => '* Campo obligatorio.',
            'password_nueva.min' => 'Mínimo 8 caracteres.',
            'password_nueva.confirmed' => 'Las contraseñas no coinciden.',
            'password_nueva.different' => 'La nueva contraseña debe ser distinta a la actual.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if (!$usuario->checkHashValue('password', $data['password_actual'])) {
            throw new ValidationException(['password_actual' => 'La contraseña actual no es correcta.']);
        }

        $usuario->password = $data['password_nueva'];
        $usuario->save();

        // Evita fijación de sesión: la sesión actual sigue siendo válida,
        // pero se le asigna un nuevo identificador tras el cambio.
        if (method_exists(session(), 'regenerate')) {
            session()->regenerate();
        }

        return [
            'estado' => 'exito',
            'mensaje' => '¡Contraseña actualizada correctamente!',
        ];
    }

    public function onGuardarFoto()
    {
        $usuario = CpanelAuth::usuario();

        if (!$usuario) {
            return redirect('/login');
        }

        $foto = Input::file('foto');

        if (!$foto) {
            throw new ValidationException(['foto' => '* Campo obligatorio.']);
        }

        $validator = Validator::make(['foto' => $foto], [
            'foto' => ['required', 'image', 'max:4096'],
        ], [
            'foto.image' => 'La foto debe ser una imagen (JPG, PNG o WEBP).',
            'foto.max' => 'La imagen no puede superar los 4 MB.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $anterior = $usuario->foto;
        $usuario->foto = $this->guardarArchivo($foto);
        $usuario->save();

        if ($anterior) {
            $this->eliminarArchivoFisico($anterior);
        }

        return [
            'estado' => 'exito',
            'mensaje' => '¡Foto de perfil actualizada!',
            'foto_url' => $usuario->foto_url,
        ];
    }

    public function onQuitarFoto()
    {
        $usuario = CpanelAuth::usuario();

        if (!$usuario) {
            return redirect('/login');
        }

        if ($usuario->foto) {
            $this->eliminarArchivoFisico($usuario->foto);
            $usuario->foto = null;
            $usuario->save();
        }

        return [
            'estado' => 'exito',
            'mensaje' => 'Foto de perfil eliminada.',
        ];
    }

    private function guardarArchivo($archivo): string
    {
        $uploadPath = base_path($this->rutaSubida);

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $nombre = time() . '_' . uniqid() . '_' . preg_replace('/[^\w.\- ]+/u', '_', $archivo->getClientOriginalName());
        $archivo->move($uploadPath, $nombre);

        return $nombre;
    }

    private function eliminarArchivoFisico(?string $nombreArchivo): void
    {
        if (!$nombreArchivo) {
            return;
        }

        $ruta = base_path($this->rutaSubida . $nombreArchivo);

        if (is_file($ruta)) {
            @unlink($ruta);
        }
    }
}
