<?php

namespace IEHAA\Documentos\Components;

use Cms\Classes\ComponentBase;
use IEHAA\Documentos\Models\Documento;
use Iehaa\Reportes\Classes\ReporteModulo;
use Illuminate\Support\Facades\Storage;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class DocumentoComponent extends ComponentBase
{
    use ReporteModulo;

    protected $rutaSubida = 'storage/app/uploads/public/documentos/';

    public function componentDetails()
    {
        return [
            'name'        => 'documentoComponent',
            'description' => 'Modulo de descargas'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['documentos'] = Documento::orderBy('nombre')->get();
    }

    public function onRegistrar()
    {
        $data = Input::all();
        $archivo = Input::file('archivo');
        $id = $data['id'] ?? null;

        $this->validaciones($data, $archivo, $id);

        $documento = $id ? Documento::find($id) : new Documento();

        if (!$documento) {
            throw new ValidationException(['nombre' => 'El documento ya no existe.']);
        }

        $documento->nombre = trim($data['nombre']);

        if ($archivo) {
            $documento->peso = $this->calcularPeso($archivo);
            $documento->archivo = $this->guardarArchivo($archivo, $documento->archivo);
        }

        $documento->save();

        return [
            '#listado' => $this->renderPartial('@listado', ['documentos' => Documento::orderBy('nombre')->get()]),
            'estado' => 'exito',
            'mensaje' => $id ? '¡Modificado correctamente!' : '¡Almacenado correctamente!'
        ];
    }

    public function onGetDocumento()
    {
        return ['documento' => Documento::find(post('id'))];
    }

    public function onEliminar()
    {
        $documento = Documento::find(intval(post('id')));

        if (!$documento) {
            return [
                '#listado' => $this->renderPartial('@listado', ['documentos' => Documento::orderBy('nombre')->get()]),
                'estado' => 'error',
                'mensaje' => 'El documento ya no existe.'
            ];
        }

        $this->eliminarArchivo($documento->archivo);
        $documento->delete();

        return [
            '#listado' => $this->renderPartial('@listado', ['documentos' => Documento::orderBy('nombre')->get()]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    /**
     * Descarga real del archivo (ruta registrada en routes.php).
     */
    public function descargar($id)
    {
        $documento = Documento::find(intval($id));

        if (!$documento || !$documento->archivo) {
            throw new ApplicationException('El documento no está disponible.');
        }

        $ruta = base_path($this->rutaSubida . $documento->archivo);

        if (!is_file($ruta)) {
            throw new ApplicationException('El archivo de este documento no se encuentra en el servidor.');
        }

        $extension = pathinfo($documento->archivo, PATHINFO_EXTENSION);
        $nombreDescarga = \Str::slug($documento->nombre) . ($extension ? '.' . $extension : '');

        return response()->download($ruta, $nombreDescarga);
    }

    public function validaciones($data, $archivo, $id = null)
    {
        $rules = [
            'nombre' => ['required', 'string', 'min:3', 'max:150'],
        ];

        if (!$id && !$archivo) {
            $rules['archivo'] = ['required'];
        }

        $validator = Validator::make($data, $rules, [
            'nombre.required'  => '* Campo obligatorio.',
            'nombre.min'       => 'Mínimo 3 caracteres.',
            'nombre.max'       => 'Máximo 150 caracteres.',
            'archivo.required' => '* Debés seleccionar un archivo.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $duplicado = Documento::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(trim($data['nombre']))])
            ->when($id, fn ($q) => $q->where('id', '!=', $id))
            ->exists();

        if ($duplicado) {
            throw new ValidationException(['nombre' => 'Ya existe una descarga con ese título.']);
        }
    }

    protected function calcularPeso($archivo): string
    {
        $bytes = $archivo->getSize();
        $unidades = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($unidades) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $unidades[$i];
    }

    public function guardarArchivo($archivo, $nombreArchivo = false)
    {
        $uploadPath = base_path($this->rutaSubida);

        if ($nombreArchivo) {
            $this->eliminarArchivo($nombreArchivo);
        }

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $nombreArchivo = time() . '_' . preg_replace('/[^\w.\- ]+/u', '_', $archivo->getClientOriginalName());
        $archivo->move($uploadPath, $nombreArchivo);

        return $nombreArchivo;
    }

    public function eliminarArchivo($nombreArchivo)
    {
        if (!$nombreArchivo) {
            return;
        }

        $ruta = base_path($this->rutaSubida . $nombreArchivo);

        if (is_file($ruta)) {
            @unlink($ruta);
        }

        Storage::delete('uploads/public/documentos/' . $nombreArchivo);
    }

    protected function datosReporte(): array
    {
        $filas = [];
        foreach (Documento::orderBy('nombre')->get() as $i => $d) {
            $filas[] = [$i + 1, $d->nombre, $d->peso];
        }

        return ['Listado de descargas', ['#', 'Título', 'Peso'], $filas, 'descargas'];
    }
}
