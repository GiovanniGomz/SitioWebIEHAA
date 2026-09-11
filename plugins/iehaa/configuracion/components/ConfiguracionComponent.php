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
    protected $rutaSubida = 'storage/app/uploads/public/configuracion/';

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
        $fondoPantalla = Input::file('fondo_pantalla');

        $validator = Validator::make($data, [
            'nombre_sitio' => 'required|min:3',
            'email_contacto' => 'nullable|email',
            'video_url' => 'nullable|url|max:500',
            'facebook_url' => 'nullable|url|max:500',
            'instagram_url' => 'nullable|url|max:500',
            'mapa_embed' => 'nullable|string|max:2000',
        ], [
            'nombre_sitio.required' => '* Campo obligatorio.',
            'email_contacto.email' => 'Correo inválido.',
            'video_url.url' => 'El enlace del video no es válido.',
            'facebook_url.url' => 'El enlace de Facebook no es válido.',
            'instagram_url.url' => 'El enlace de Instagram no es válido.',
        ]);

        if ($fondoPantalla) {
            $validadorImagen = Validator::make(['fondo_pantalla' => $fondoPantalla], [
                'fondo_pantalla' => 'image|max:8192',
            ], [
                'fondo_pantalla.image' => 'El fondo debe ser una imagen (JPG, PNG o WEBP).',
                'fondo_pantalla.max' => 'La imagen no puede superar los 8 MB.',
            ]);

            if ($validadorImagen->fails()) {
                throw new ValidationException($validadorImagen);
            }
        }

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $configuracion = Configuracion::actual();
        $configuracion->nombre_sitio = $data['nombre_sitio'];
        $configuracion->descripcion_sitio = $data['descripcion_sitio'] ?? '';
        $configuracion->email_contacto = $data['email_contacto'] ?? '';
        $configuracion->telefono_contacto = $data['telefono_contacto'] ?? '';
        $configuracion->direccion = $data['direccion'] ?? '';
        $configuracion->texto_nosotros = $data['texto_nosotros'] ?? '';
        $configuracion->titulo_patrimonio = $data['titulo_patrimonio'] ?? '';
        $configuracion->texto_patrimonio = $data['texto_patrimonio'] ?? '';
        $configuracion->video_url = trim($data['video_url'] ?? '') ?: null;

        // Aceptar tanto la URL del mapa como un <iframe> completo pegado.
        $mapa = trim($data['mapa_embed'] ?? '');
        if ($mapa !== '' && preg_match('/src=["\']([^"\']+)["\']/', $mapa, $m)) {
            $mapa = $m[1];
        }
        $configuracion->mapa_embed = $mapa ?: null;
        $configuracion->facebook_url = trim($data['facebook_url'] ?? '') ?: null;
        $configuracion->instagram_url = trim($data['instagram_url'] ?? '') ?: null;

        if ($fondoPantalla) {
            $anterior = $configuracion->fondo_pantalla;
            $configuracion->fondo_pantalla = $this->guardarArchivo($fondoPantalla);

            if ($anterior) {
                $this->eliminarArchivoFisico($anterior);
            }
        } elseif (!empty($data['quitar_fondo_pantalla']) && $configuracion->fondo_pantalla) {
            $this->eliminarArchivoFisico($configuracion->fondo_pantalla);
            $configuracion->fondo_pantalla = null;
        }

        $configuracion->save();

        return [
            'estado' => 'exito',
            'mensaje' => '¡Configuración guardada correctamente!',
            'fondo_pantalla_url' => $configuracion->fondo_pantalla_url,
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
