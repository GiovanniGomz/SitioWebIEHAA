<?php

namespace Iehaa\Proyectos\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Investigadores\Models\Investigador;
use Iehaa\Proyectos\Models\Proyecto;
use Iehaa\Reportes\Classes\ReporteModulo;
use Illuminate\Support\Facades\Storage;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class ProyectoComponent extends ComponentBase
{
    use ReporteModulo;

    protected $rutaSubida = 'storage/app/uploads/public/proyectos/';

    public function componentDetails()
    {
        return [
            'name'        => 'proyectoComponent',
            'description' => 'Modulo de proyectos de investigación'
        ];
    }

    public function defineProperties()
    {
        return [
            'limite' => [
                'title' => 'Límite de registros',
                'description' => 'Usado en la página pública para mostrar solo los más recientes',
                'default' => '',
                'type' => 'string',
            ],
        ];
    }

    public function onRun()
    {
        if (session()->has('error_descarga')) {
            $this->page['errorDescarga'] = session()->pull('error_descarga');
        }

        $this->page['proyectos'] = $this->obtenerTodos();
        $this->page['investigadores'] = Investigador::all();
    }

    public function obtenerTodos()
    {
        $query = Proyecto::with('investigador')->orderByDesc('id');

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
            $proyecto = Proyecto::find($id);
            $mensaje = '¡Modificado correctamente!';
        } else {
            $proyecto = new Proyecto();
            $mensaje = '¡Almacenado correctamente!';
        }

        if (!$proyecto) {
            throw new ValidationException(['titulo' => 'El proyecto ya no existe.']);
        }

        $proyecto->titulo = $data['titulo'];
        $proyecto->descripcion = $data['descripcion'];
        $proyecto->detalle = $data['detalle'] ?? '';
        $proyecto->investigador_id = $data['investigador_id'];

        if ($archivo) {
            $anterior = $proyecto->archivo;
            $proyecto->archivo = $this->guardarArchivo($archivo);

            if ($anterior) {
                $this->eliminarArchivo($anterior);
            }
        } elseif (!empty($data['quitar_archivo']) && $proyecto->archivo) {
            $this->eliminarArchivo($proyecto->archivo);
            $proyecto->archivo = null;
        }

        $proyecto->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'proyectos' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    public function onGetProyecto()
    {
        $proyecto = Proyecto::find(post('id'));

        return ['proyecto' => $proyecto];
    }

    public function onEliminar()
    {
        $proyecto = Proyecto::find(intval(post('id')));

        if ($proyecto) {
            if ($proyecto->archivo) {
                $this->eliminarArchivo($proyecto->archivo);
            }
            $proyecto->delete();
        }

        return [
            '#listado' => $this->renderPartial('@listado', [
                'proyectos' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => $proyecto ? '¡Eliminado con exito!' : 'El proyecto ya no existe.'
        ];
    }

    /**
     * Descarga real del documento (ruta registrada en routes.php). Es de
     * acceso público: el documento de un proyecto se descarga también desde
     * la página de investigaciones.
     */
    public function descargar($id)
    {
        $proyecto = Proyecto::find(intval($id));

        if (!$proyecto || !$proyecto->archivo) {
            return redirect('/proyectos')->with('error_descarga', 'El documento no está disponible.');
        }

        $ruta = base_path($this->rutaSubida . $proyecto->archivo);

        if (!is_file($ruta)) {
            return redirect('/proyectos')->with('error_descarga', 'El archivo de este proyecto no se encuentra en el servidor.');
        }

        $extension = pathinfo($proyecto->archivo, PATHINFO_EXTENSION);
        $nombreDescarga = \Str::slug($proyecto->titulo) . ($extension ? '.' . $extension : '');

        return response()->download($ruta, $nombreDescarga);
    }

    public function validaciones($data)
    {
        $validator = Validator::make($data, [
            'titulo' => 'required|min:3',
            'descripcion' => 'required',
            'investigador_id' => 'required',
        ], [
            'titulo.required' => '* Campo obligatorio.',
            'titulo.min' => 'Mínimo 3 caracteres.',
            'descripcion.required' => '* Campo obligatorio.',
            'investigador_id.required' => '* Campo obligatorio.',
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

    private function eliminarArchivo(?string $nombreArchivo): void
    {
        if (!$nombreArchivo) {
            return;
        }

        Storage::delete('uploads/public/proyectos/' . $nombreArchivo);
    }

    protected function datosReporte(): array
    {
        $filas = [];
        $proyectos = Proyecto::with('investigador')->orderByDesc('id')->get();

        foreach ($proyectos as $i => $p) {
            $filas[] = [
                $i + 1,
                $p->titulo,
                $p->investigador ? trim($p->investigador->nombre . ' ' . $p->investigador->apellido) : '—',
                \Str::limit((string) $p->descripcion, 120),
                $p->archivo ? 'Sí' : 'No',
            ];
        }

        return ['Listado de proyectos de investigación', ['#', 'Título', 'Investigador', 'Resumen del proyecto', 'Documento'], $filas, 'proyectos'];
    }
}
