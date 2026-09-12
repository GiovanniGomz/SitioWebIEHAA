<?php

namespace Iehaa\Anuncios\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Anuncios\Models\Anuncio;
use Iehaa\Reportes\Classes\ReporteModulo;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class AnuncioComponent extends ComponentBase
{
    use ReporteModulo;

    protected $rutaSubida = 'storage/app/uploads/public/anuncios/';

    public function componentDetails()
    {
        return [
            'name'        => 'anuncioComponent',
            'description' => 'Anuncios de la página principal'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['anuncios'] = $this->obtenerTodos();
    }

    public function obtenerTodos()
    {
        return Anuncio::orderBy('orden')->orderByDesc('id')->get();
    }

    public function onRegistrar()
    {
        $data = Input::all();
        $imagen = Input::file('imagen');
        $id = $data['id'] ?? null;

        $this->validaciones($data);

        $anuncio = $id ? Anuncio::find($id) : new Anuncio();

        if (!$anuncio) {
            throw new ValidationException(['titulo' => 'El anuncio ya no existe.']);
        }

        $anuncio->titulo = trim($data['titulo']);
        $anuncio->texto = trim($data['texto']);
        $anuncio->video_url = trim($data['video_url'] ?? '') ?: null;
        $anuncio->orden = intval($data['orden'] ?? 0);
        $anuncio->activo = isset($data['activo']);

        if ($imagen) {
            $anterior = $anuncio->imagen;
            $anuncio->imagen = $this->guardarArchivo($imagen);

            if ($anterior) {
                $this->eliminarArchivoFisico($anterior);
            }
        } elseif (!empty($data['quitar_imagen']) && $anuncio->imagen) {
            $this->eliminarArchivoFisico($anuncio->imagen);
            $anuncio->imagen = null;
        }

        $anuncio->save();

        return [
            '#listado' => $this->renderPartial('@listado', ['anuncios' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => $id ? '¡Modificado correctamente!' : '¡Almacenado correctamente!'
        ];
    }

    public function onGetAnuncio()
    {
        return ['anuncio' => Anuncio::find(post('id'))];
    }

    public function onEliminar()
    {
        $anuncio = Anuncio::find(intval(post('id')));

        if (!$anuncio) {
            return [
                '#listado' => $this->renderPartial('@listado', ['anuncios' => $this->obtenerTodos()]),
                'estado' => 'error',
                'mensaje' => 'El anuncio ya no existe.',
            ];
        }

        if ($anuncio->imagen) {
            $this->eliminarArchivoFisico($anuncio->imagen);
        }

        $anuncio->delete();

        return [
            '#listado' => $this->renderPartial('@listado', ['anuncios' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function validaciones($data)
    {
        $validator = Validator::make($data, [
            'titulo' => ['required', 'string', 'min:3', 'max:150'],
            'texto' => ['required', 'string', 'min:10'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'orden' => ['nullable', 'integer'],
        ], [
            'titulo.required' => '* Campo obligatorio.',
            'titulo.min' => 'Mínimo 3 caracteres.',
            'texto.required' => '* Campo obligatorio.',
            'texto.min' => 'Escribí al menos 10 caracteres.',
            'video_url.url' => 'Ingresá un enlace válido (debe empezar con http:// o https://).',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
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

    protected function datosReporte(): array
    {
        $filas = [];
        foreach ($this->obtenerTodos() as $i => $a) {
            $filas[] = [
                $i + 1,
                $a->titulo,
                $a->activo ? 'Activo' : 'Inactivo',
                $a->orden,
                $a->created_at ? $a->created_at->format('d/m/Y') : '—',
            ];
        }

        return [
            'Anuncios de la página principal',
            ['#', 'Título', 'Estado', 'Orden', 'Fecha de creación'],
            $filas,
            'anuncios',
        ];
    }
}
