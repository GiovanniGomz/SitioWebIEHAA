<?php

namespace Iehaa\Fabio\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Carpetas\Models\Carpeta;
use Iehaa\Fabio\Models\Fabio;
use Iehaa\Folders\Models\Folder;
use Illuminate\Support\Facades\Storage;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

use Barryvdh\DomPDF\Facade\Pdf;

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FabioComponent extends ComponentBase
{
    use \Iehaa\Reportes\Classes\ReporteModulo;

    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'fabioComponent',
            'description' => 'Modulo de archivos Fabio Castillo'
        ];
    }

    /**
     * Returns the properties provided by the component
     */
    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['documentos'] = $this->obtenerDocumentos();
    }

    public function obtenerDocumentos()
    {
        $url = get('id');

        \Log::info('Esta es la url de fabio');
        \Log::info($url);

        $documentos = Fabio::whereHas('Folder', function ($query) use ($url) {
            $query->where('url', $url);
        })->get();

        \Log::info($documentos);

        return $documentos;
    }

    public function onRegistrar()
    {
        \Log::info("Voy a guardar");

        $data = Input::all();
        $archivo = Input::file('archivo');

        //Llave primaria
        $id = $data['id'];


        if ($id) { //Actualizando
            $this->validacionesModificar($data);

            $fabio = Fabio::find($id);

            if ($archivo) {
                $fabio->archivo = $this->guardarArchivo($archivo, $fabio->archivo);
            }

            $fabio->nombre = $data['nombre'];

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $url = get('id');

            \Log::info("URL al almacenar");
            \Log::info($url);

            $folder = Folder::where('url', $url)->first();

            \Log::info($folder);

            if (!$folder) {
                exit;
            }

            $fabio = new Fabio();
            $fabio->nombre = $data['nombre'];
            $fabio->folder_id = $folder->id;

            $this->validacionesRegistrar($data);

            $fabio->archivo = $this->guardarArchivo($archivo);

            $mensaje = '¡Almacenado correctamente!';
        }

        $fabio->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'documentos' => $this->obtenerDocumentos()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    function onGetDocumento()
    {
        $id = post('id');
        $fabio = Fabio::find($id);

        return ['documento' => $fabio];
    }

    function onEliminar()
    {
        $id = post('id');
        $id = intval($id);
        $fabio = Fabio::find($id);

        $this->eliminarArchivo($fabio->archivo);

        $fabio->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'documentos' => $this->obtenerDocumentos()
            ]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function validacionesRegistrar($data)
    {
        $rules = [
            'nombre' => 'required|min:3',
            'archivo' => 'required'
        ];

        $customMessages = [
            'nombre.required' => '* Campo obligatorio.',
            'nombre.min'      => 'Minimo 3 caracteres',
            'archivo.required' => '* Campo obligatorio'
        ];

        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function validacionesModificar($data)
    {
        $rules = [
            'nombre' => 'required|min:3',
        ];

        $customMessages = [
            'nombre.required' => '* Campo obligatorio.',
            'nombre.min'      => 'Minimo 3 caracteres',
        ];

        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function guardarArchivo($archivo, $nombreArchivo = false)
    {
        $uploadPath = 'storage/app/uploads/public/fabio/';

        if ($nombreArchivo) $this->eliminarArchivo($nombreArchivo);

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
        $archivo->move($uploadPath, $nombreArchivo);
        return $nombreArchivo;
    }

    public function eliminarArchivo($nombreArchivo)
    {
        Storage::delete('uploads/public/fabio/' . $nombreArchivo);
    }

    protected function datosReporte(): array
    {
        $filas = [];
        foreach (\Iehaa\Fabio\Models\Fabio::with('Folder.Carpeta.Gaveta.Archivero')->get() as $i => $r) {
            $filas[] = [
                $i + 1,
                $r->nombre,
                $r->archivo,
                optional($r->Folder)->nombre ?: '—',
                optional(optional($r->Folder)->Carpeta)->nombre ?: '—',
                optional(optional(optional($r->Folder)->Carpeta)->Gaveta)->codigo ? 'Gaveta ' . $r->Folder->Carpeta->Gaveta->codigo : '—',
                optional(optional(optional(optional($r->Folder)->Carpeta)->Gaveta)->Archivero)->codigo ? 'Archivero ' . $r->Folder->Carpeta->Gaveta->Archivero->codigo : '—',
            ];
        }

        return ['Listado de documentos — Fabio Castillo', ['#', 'Documento', 'Archivo', 'Folder', 'Carpeta', 'Gaveta', 'Archivero'], $filas, 'documentos_fabio_castillo'];
    }
}
