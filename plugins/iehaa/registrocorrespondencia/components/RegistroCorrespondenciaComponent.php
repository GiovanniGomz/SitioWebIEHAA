<?php

namespace Iehaa\Registrocorrespondencia\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Categoriacorrespondencia\Models\CategoriaCorrespondencia;
use Iehaa\Facultades\Models\Facultad;
use Iehaa\Registrocorrespondencia\Models\RegistroCorrespondencia;
use Iehaa\Reportes\Classes\ReporteModulo;
use Illuminate\Support\Facades\Storage;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class RegistroCorrespondenciaComponent extends ComponentBase
{
    use ReporteModulo;

    protected $rutaSubida = 'storage/app/uploads/public/registro-correspondencia/';

    public function componentDetails()
    {
        return [
            'name'        => 'registroCorrespondenciaComponent',
            'description' => 'Registro de correspondencia enviada y recibida'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        if (session()->has('error_descarga')) {
            $this->page['errorDescarga'] = session()->pull('error_descarga');
        }

        $this->page['registros'] = $this->obtenerTodos();
        $this->page['facultades'] = Facultad::orderBy('nombre')->get();
        $this->page['categorias'] = CategoriaCorrespondencia::orderBy('nombre')->get();
    }

    public function obtenerTodos()
    {
        return RegistroCorrespondencia::with(['facultad', 'categoria'])->orderByDesc('fecha')->orderByDesc('id')->get();
    }

    public function onRegistrar()
    {
        $data = Input::all();
        $archivo = Input::file('archivo');
        $id = $data['id'] ?? null;

        $this->validaciones($data, $archivo, $id);

        $registro = $id ? RegistroCorrespondencia::find($id) : new RegistroCorrespondencia();

        if (!$registro) {
            throw new ValidationException(['nombre' => 'El registro ya no existe.']);
        }

        $registro->nombre = trim($data['nombre']);
        $registro->facultad_id = $data['facultad_id'];
        $registro->tipo = $data['tipo'];
        $registro->fecha = $data['fecha'];
        $registro->categoria_correspondencia_id = $data['categoria_correspondencia_id'];

        if ($archivo) {
            $anterior = $registro->archivo;
            $registro->archivo = $this->guardarArchivo($archivo);

            if ($anterior) {
                $this->eliminarArchivo($anterior);
            }
        }

        $registro->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'registros' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => $id ? '¡Modificado correctamente!' : '¡Almacenado correctamente!'
        ];
    }

    public function onGetRegistro()
    {
        return ['registro' => RegistroCorrespondencia::find(post('id'))];
    }

    public function onEliminar()
    {
        $registro = RegistroCorrespondencia::find(intval(post('id')));

        if (!$registro) {
            return [
                '#listado' => $this->renderPartial('@listado', ['registros' => $this->obtenerTodos()]),
                'estado' => 'error',
                'mensaje' => 'El registro ya no existe.'
            ];
        }

        $this->eliminarArchivo($registro->archivo);
        $registro->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'registros' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    /**
     * Descarga real del archivo (ruta registrada en routes.php).
     */
    public function descargar($id)
    {
        $registro = RegistroCorrespondencia::find(intval($id));

        if (!$registro || !$registro->archivo) {
            return redirect('/correspondencia')->with('error_descarga', 'El documento no está disponible.');
        }

        $ruta = base_path($this->rutaSubida . $registro->archivo);

        if (!is_file($ruta)) {
            return redirect('/correspondencia')->with('error_descarga', 'El archivo de este registro no se encuentra en el servidor.');
        }

        $extension = pathinfo($registro->archivo, PATHINFO_EXTENSION);
        $nombreDescarga = \Str::slug($registro->nombre) . ($extension ? '.' . $extension : '');

        return response()->download($ruta, $nombreDescarga);
    }

    public function validaciones($data, $archivo, $id = null)
    {
        $rules = [
            'nombre' => ['required', 'string', 'max:150', 'min:3'],
            'facultad_id' => ['required'],
            'tipo' => ['required', 'in:' . implode(',', RegistroCorrespondencia::TIPOS)],
            'fecha' => ['required', 'date'],
            'categoria_correspondencia_id' => ['required'],
        ];

        if (!$id && !$archivo) {
            $rules['archivo'] = ['required'];
        }

        $validator = Validator::make($data, $rules, [
            'nombre.required' => '* Campo obligatorio.',
            'nombre.min' => 'Mínimo 3 caracteres.',
            'facultad_id.required' => '* Campo obligatorio.',
            'tipo.required' => '* Campo obligatorio.',
            'tipo.in' => 'Seleccioná si fue enviado o recibido.',
            'fecha.required' => '* Campo obligatorio.',
            'fecha.date' => 'Fecha inválida.',
            'categoria_correspondencia_id.required' => '* Campo obligatorio.',
            'archivo.required' => '* Campo obligatorio.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if (!Facultad::find($data['facultad_id'])) {
            throw new ValidationException(['facultad_id' => 'La facultad seleccionada ya no existe.']);
        }

        if (!CategoriaCorrespondencia::find($data['categoria_correspondencia_id'])) {
            throw new ValidationException(['categoria_correspondencia_id' => 'La categoría seleccionada ya no existe.']);
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

        Storage::delete('uploads/public/registro-correspondencia/' . $nombreArchivo);
    }

    protected function datosReporte(): array
    {
        $filas = [];

        foreach ($this->obtenerTodos() as $i => $r) {
            $filas[] = [
                $i + 1,
                $r->nombre,
                optional($r->facultad)->nombre ?: '—',
                $r->tipo_etiqueta,
                optional($r->categoria)->nombre ?: '—',
                $r->fecha ? $r->fecha->format('d/m/Y') : '—',
            ];
        }

        return [
            'Registro de correspondencia',
            ['#', 'Nombre', 'Facultad', 'Tipo', 'Categoría', 'Fecha'],
            $filas,
            'correspondencia',
        ];
    }
}
