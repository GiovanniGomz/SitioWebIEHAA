<?php

namespace Iehaa\Carpetas\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Carpetas\Models\Carpeta;
use Iehaa\Gavetas\Models\Gaveta;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

use Barryvdh\DomPDF\Facade\Pdf;

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CarpetaComponent extends ComponentBase
{
    use \Iehaa\Reportes\Classes\ReporteModulo;

    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'carpetaComponent',
            'description' => 'Modulo de carpetas'
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
        $this->page['carpetas'] = $this->obtenerCarpetas();
    }

    public function obtenerCarpetas()
    {
        $url = get('id');

        return Carpeta::whereHas('Gaveta', function ($query) use ($url) {
            $query->where('url', $url);
        })->get();
    }

    public function onRegistrar()
    {

        $data = Input::all();

        //Llave primaria
        $id = $data['id'];


        if ($id) { //Actualizando
            $this->validaciones($data);

            $carpeta = Carpeta::find($id);
            $carpeta->nombre = $data['nombre'];

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $url = get('id');
            $gaveta = Gaveta::where('url', $url)->first();

            if (!$gaveta) {
                exit;
            }

            $carpeta = new Carpeta();
            $carpeta->nombre = $data['nombre'];
            $carpeta->url = $this->generarURL();
            $carpeta->gaveta_id = $gaveta->id;

            \Log::info("Datos de carpeta");
            \Log::info($carpeta);

            $this->validaciones($data);

            $mensaje = '¡Almacenado correctamente!';
        }

        $carpeta->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'carpetas' => $this->obtenerCarpetas()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    function onGetCarpeta()
    {
        $id = post('id');
        $carpeta = Carpeta::find($id);

        return ['carpeta' => $carpeta];
    }

    function onEliminar()
    {
        \Log::info("Acceso a onEliminar");

        $id = post('id');
        $id = intval($id);

        $carpeta = Carpeta::find($id);

        $carpeta->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'carpetas' => $this->obtenerCarpetas()
            ]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function generarURL()
    {
        $url = md5(uniqid());
        $urlHash = $this->hash($url);

        return $urlHash;
    }

    public function hash($valor)
    {
        $hash = password_hash($valor, PASSWORD_BCRYPT);
        return $hash;
    }

    public function validaciones($data)
    {
        $rules = [
            'nombre' => 'required|min:3',
        ];

        $customMessages = [
            'nombre.required' => '* Campo obligatorio.',
            'nombre.min'      => 'Minimo 3 caracteres'
        ];

        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    protected function datosReporte(): array
    {
        $filas = [];
        foreach (\Iehaa\Carpetas\Models\Carpeta::with('Gaveta.Archivero')->get() as $i => $r) {
            $filas[] = [$i + 1, $r->nombre, optional($r->Gaveta)->codigo ? 'Gaveta ' . $r->Gaveta->codigo : '—', optional(optional($r->Gaveta)->Archivero)->codigo ? 'Archivero ' . $r->Gaveta->Archivero->codigo : '—'];
        }

        return ['Listado de carpetas — Fabio Castillo', ['#', 'Carpeta', 'Gaveta', 'Archivero'], $filas, 'carpetas'];
    }
}
