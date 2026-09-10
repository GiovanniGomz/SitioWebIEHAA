<?php

namespace Iehaa\Publicaciones\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Investigadores\Models\Investigador;
use Iehaa\Publicaciones\Models\Publicacion;
use Iehaa\Reportes\Classes\ReporteModulo;
use Iehaa\Tipopublicaciones\Models\TipoPublicaciones;
use Illuminate\Support\Facades\Storage;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class PublicacionComponent extends ComponentBase
{
    use ReporteModulo;

    public function componentDetails()
    {
        return [
            'name'        => 'publicacionComponent',
            'description' => 'Modulo de publicaciones científicas'
        ];
    }

    public function defineProperties()
    {
        return [
            'limite' => [
                'title' => 'Límite de registros',
                'description' => 'Usado en la página pública para mostrar solo las más recientes',
                'default' => '',
                'type' => 'string',
            ],
        ];
    }

    public function onRun()
    {
        $this->page['publicaciones'] = $this->obtenerTodas();
        $this->page['investigadores'] = Investigador::all();
        $this->page['tiposPublicacion'] = TipoPublicaciones::all();
    }

    public function obtenerTodas()
    {
        $query = Publicacion::with(['investigador', 'tipo_publicacion'])->orderByDesc('id');

        if ($limite = $this->property('limite')) {
            $query->limit((int) $limite);
        }

        return $query->get();
    }

    public function onRegistrar()
    {
        $data = Input::all();
        $archivo = Input::file('archivo');
        $id = $data['id'] ?? null;

        $this->validaciones($data);

        $publicacion = $id ? Publicacion::find($id) : new Publicacion();

        if (!$publicacion) {
            throw new ValidationException(['titulo' => 'La publicación ya no existe.']);
        }

        $publicacion->titulo = trim($data['titulo']);
        $publicacion->descripcion = trim($data['descripcion']);
        $publicacion->url = trim($data['url'] ?? '') ?: null;
        $publicacion->fecha = $data['fecha'] ?? null;
        $publicacion->tipo_publicacion_id = $data['tipo_publicacion_id'];
        $publicacion->investigador_id = $data['investigador_id'];

        if ($archivo) {
            $publicacion->archivo = $this->guardarArchivo($archivo, $publicacion->archivo);
        }

        $publicacion->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'publicaciones' => $this->obtenerTodas()
            ]),
            'estado' => 'exito',
            'mensaje' => $id ? '¡Modificado correctamente!' : '¡Almacenado correctamente!'
        ];
    }

    public function onGetPublicacion()
    {
        return ['publicacion' => Publicacion::find(post('id'))];
    }

    public function onEliminar()
    {
        $publicacion = Publicacion::find(intval(post('id')));

        if ($publicacion) {
            if ($publicacion->archivo) {
                $this->eliminarArchivo($publicacion->archivo);
            }
            $publicacion->delete();
        }

        return [
            '#listado' => $this->renderPartial('@listado', [
                'publicaciones' => $this->obtenerTodas()
            ]),
            'estado' => 'exito',
            'mensaje' => $publicacion ? '¡Eliminado con exito!' : 'La publicación ya no existe.'
        ];
    }

    public function validaciones($data)
    {
        $validator = Validator::make($data, [
            'titulo' => ['required', 'string', 'min:3', 'max:255', 'regex:/^(?=.*[\pL\pN]).+$/us'],
            'descripcion' => ['required', 'string', 'min:3', 'regex:/^(?=.*[\pL\pN]).+$/us'],
            'url' => ['nullable', 'url', 'max:500'],
            'tipo_publicacion_id' => 'required',
            'investigador_id' => 'required',
        ], [
            'titulo.required' => '* Campo obligatorio.',
            'titulo.min' => 'Mínimo 3 caracteres.',
            'titulo.regex' => 'El título debe contener texto o números.',
            'descripcion.required' => '* Campo obligatorio.',
            'descripcion.regex' => 'La descripción debe contener texto o números.',
            'url.url' => 'Ingresá un enlace válido (debe empezar con http:// o https://).',
            'tipo_publicacion_id.required' => '* Seleccioná un tipo.',
            'investigador_id.required' => '* Seleccioná un investigador.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function guardarArchivo($archivo, $nombreArchivo = false)
    {
        $uploadPath = 'storage/app/uploads/public/publicaciones/';

        if ($nombreArchivo) {
            $this->eliminarArchivo($nombreArchivo);
        }

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
        $archivo->move($uploadPath, $nombreArchivo);

        return $nombreArchivo;
    }

    public function eliminarArchivo($nombreArchivo)
    {
        Storage::delete('uploads/public/publicaciones/' . $nombreArchivo);
    }

    protected function datosReporte(): array
    {
        $filas = [];
        $publicaciones = Publicacion::with(['investigador', 'tipo_publicacion'])->orderByDesc('id')->get();

        foreach ($publicaciones as $i => $p) {
            $filas[] = [
                $i + 1,
                $p->titulo,
                optional($p->tipo_publicacion)->nombre ?: '—',
                $p->investigador ? trim($p->investigador->nombre . ' ' . $p->investigador->apellido) : '—',
                $p->fecha ? $p->fecha->format('d/m/Y') : '—',
                $p->url ?: '—',
            ];
        }

        return ['Listado de publicaciones', ['#', 'Título', 'Tipo', 'Investigador', 'Fecha', 'Enlace'], $filas, 'publicaciones'];
    }
}
