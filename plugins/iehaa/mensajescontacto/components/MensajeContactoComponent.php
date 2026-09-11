<?php

namespace Iehaa\Mensajescontacto\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Mensajescontacto\Models\MensajeContacto;
use Iehaa\Reportes\Classes\ReporteModulo;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class MensajeContactoComponent extends ComponentBase
{
    use ReporteModulo;

    public function componentDetails()
    {
        return [
            'name'        => 'mensajeContactoComponent',
            'description' => 'Mensajes de contacto del sitio público'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['mensajes'] = $this->obtenerTodos();
    }

    public function obtenerTodos()
    {
        return MensajeContacto::orderByDesc('created_at')->get();
    }

    public static function noLeidos(): int
    {
        return MensajeContacto::where('leido', false)->count();
    }

    /**
     * Recibe el formulario de contacto de la página pública. Sin autenticación:
     * cualquier visitante puede escribir.
     */
    public function onEnviar()
    {
        $data = Input::all();

        $validator = Validator::make($data, [
            'nombre'  => ['required', 'string', 'min:3', 'max:120', 'regex:/^[\pL\s.\'\-]+$/u'],
            'email'   => ['required', 'email', 'max:150'],
            'asunto'  => ['required', 'string', 'min:3', 'max:200'],
            'mensaje' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'nombre.required'  => '* Campo obligatorio.',
            'nombre.regex'     => 'El nombre solo admite letras y espacios.',
            'email.required'   => '* Campo obligatorio.',
            'email.email'      => 'Correo inválido.',
            'asunto.required'  => '* Campo obligatorio.',
            'asunto.min'       => 'Mínimo 3 caracteres.',
            'mensaje.required' => '* Campo obligatorio.',
            'mensaje.min'      => 'Contanos un poco más (mínimo 10 caracteres).',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        MensajeContacto::create([
            'nombre'  => trim($data['nombre']),
            'email'   => trim($data['email']),
            'asunto'  => trim($data['asunto']),
            'mensaje' => trim($data['mensaje']),
            'leido'   => false,
        ]);

        return [
            'estado' => 'exito',
            'mensaje' => '¡Gracias por escribirnos! Tu mensaje fue enviado correctamente.',
        ];
    }

    public function onMarcarLeido()
    {
        $mensaje = MensajeContacto::find(intval(post('id')));

        if ($mensaje && !$mensaje->leido) {
            $mensaje->leido = true;
            $mensaje->save();
        }

        return [
            '#listado' => $this->renderPartial('@listado', ['mensajes' => $this->obtenerTodos()]),
            'noLeidos' => self::noLeidos(),
        ];
    }

    public function onEliminar()
    {
        $mensaje = MensajeContacto::find(intval(post('id')));

        if (!$mensaje) {
            return [
                '#listado' => $this->renderPartial('@listado', ['mensajes' => $this->obtenerTodos()]),
                'estado' => 'error',
                'mensaje' => 'El mensaje ya no existe.',
            ];
        }

        $mensaje->delete();

        return [
            '#listado' => $this->renderPartial('@listado', ['mensajes' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!',
            'noLeidos' => self::noLeidos(),
        ];
    }

    protected function datosReporte(): array
    {
        $filas = [];
        foreach ($this->obtenerTodos() as $i => $m) {
            $filas[] = [
                $i + 1,
                $m->nombre,
                $m->email,
                $m->asunto,
                $m->leido ? 'Leído' : 'Sin leer',
                $m->created_at ? $m->created_at->format('d/m/Y H:i') : '—',
            ];
        }

        return [
            'Mensajes de contacto',
            ['#', 'Nombre', 'Correo', 'Asunto', 'Estado', 'Fecha'],
            $filas,
            'mensajes_contacto',
        ];
    }
}
