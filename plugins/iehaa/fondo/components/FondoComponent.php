<?php

namespace Iehaa\Fondo\Components;

use Cms\Classes\ComponentBase;

use Illuminate\Support\Facades\Storage;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

use Barryvdh\DomPDF\Facade\Pdf;
use Iehaa\Coleccion\Models\Coleccion;
use Iehaa\Fondo\Models\Fondo;

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FondoComponent extends ComponentBase
{
    use \Iehaa\Reportes\Classes\ReporteModulo;

    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'fondoComponent',
            'description' => 'Modulo de fondo bibliográfico'
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

        \Log::info('Esta es la url de fondo');
        \Log::info($url);

        $documentos = Fondo::whereHas('Coleccion', function ($query) use ($url) {
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

            $fondo = Fondo::find($id);

            if ($archivo) {
                $fondo->archivo = $this->guardarArchivo($archivo, $fondo->archivo);
            }

            $fondo->nombre = $data['nombre'];

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $url = get('id');

            \Log::info("URL al almacenar");
            \Log::info($url);

            $coleccion = Coleccion::where('url', $url)->first();

            \Log::info($coleccion);

            if (!$coleccion) {
                exit;
            }

            $fondo = new Fondo();
            $fondo->nombre = $data['nombre'];
            $fondo->coleccion_id = $coleccion->id;

            $this->validacionesRegistrar($data);

            $fondo->archivo = $this->guardarArchivo($archivo);

            $mensaje = '¡Almacenado correctamente!';
        }

        $fondo->save();

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
        $fondo = Fondo::find($id);

        return ['documento' => $fondo];
    }

    function onEliminar()
    {
        $id = post('id');
        $id = intval($id);
        $fondo = Fondo::find($id);

        $this->eliminarArchivo($fondo->archivo);

        $fondo->delete();

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
        $uploadPath = 'storage/app/uploads/public/fondo/';

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
        Storage::delete('uploads/public/fondo/' . $nombreArchivo);
    }

    protected function datosReporte(): array
    {
        $filas = [];
        foreach (\Iehaa\Fondo\Models\Fondo::with('Coleccion.Anaquel.Estante')->get() as $i => $r) {
            $filas[] = [
                $i + 1,
                $r->nombre,
                $r->archivo,
                optional($r->Coleccion)->nombre ?: '—',
                optional(optional($r->Coleccion)->Anaquel)->codigo ? 'Anaquel ' . $r->Coleccion->Anaquel->codigo : '—',
                optional(optional(optional($r->Coleccion)->Anaquel)->Estante)->codigo ? 'Estante ' . $r->Coleccion->Anaquel->Estante->codigo : '—',
            ];
        }

        return ['Listado de documentos — Fondo Bibliográfico', ['#', 'Documento', 'Archivo', 'Colección', 'Anaquel', 'Estante'], $filas, 'documentos_fondo_bibliografico'];
    }
}
