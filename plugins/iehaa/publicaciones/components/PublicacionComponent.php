<?php

namespace Iehaa\Publicaciones\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Investigadores\Models\Investigador;
use Iehaa\Publicaciones\Models\Publicacion;
use Iehaa\Tipopublicaciones\Models\TipoPublicaciones;
use Illuminate\Support\Facades\Storage;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class PublicacionComponent extends ComponentBase
{
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

        if ($id) {
            $publicacion = Publicacion::find($id);
            $mensaje = '¡Modificado correctamente!';
        } else {
            $publicacion = new Publicacion();
            $mensaje = '¡Almacenado correctamente!';
        }

        $publicacion->titulo = $data['titulo'];
        $publicacion->descripcion = $data['descripcion'];
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
            'mensaje' => $mensaje
        ];
    }

    public function onGetPublicacion()
    {
        $publicacion = Publicacion::find(post('id'));

        return ['publicacion' => $publicacion];
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
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function validaciones($data)
    {
        $validator = Validator::make($data, [
            'titulo' => 'required|min:3',
            'descripcion' => 'required',
            'tipo_publicacion_id' => 'required',
            'investigador_id' => 'required',
        ], [
            'titulo.required' => '* Campo obligatorio.',
            'titulo.min' => 'Mínimo 3 caracteres.',
            'descripcion.required' => '* Campo obligatorio.',
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
}
