<?php

namespace Iehaa\Gavetas\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Archiveros\Models\Archivero;
use Iehaa\Gavetas\Models\Gaveta;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

use Barryvdh\DomPDF\Facade\Pdf;

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class GavetaComponent extends ComponentBase
{
    use \Iehaa\Reportes\Classes\ReporteModulo;

    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'gavetaComponent',
            'description' => 'Modulo de gavetas'
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
        $this->page['gavetas'] = $this->obtenerGavetas();
    }

    public function obtenerGavetas()
    {
        $url = get('id');

        return Gaveta::whereHas('Archivero', function ($query) use ($url) {
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

            $gaveta = Gaveta::find($id);
            $gaveta->codigo = $data['codigo'];

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $url = get('id');
            $archivero = Archivero::where('url', $url)->first();

            if (!$archivero) {
                exit;
            }

            $gaveta = new Gaveta();
            $gaveta->codigo = $data['codigo'];
            $gaveta->url = $this->generarURL();
            $gaveta->archivero_id = $archivero->id;

            $this->validaciones($data);

            $mensaje = '¡Almacenado correctamente!';
        }

        $gaveta->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'gavetas' => $this->obtenerGavetas()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    function onGetGaveta()
    {
        $id = post('id');
        $gaveta = Gaveta::find($id);

        return ['gaveta' => $gaveta];
    }

    function onEliminar()
    {
        \Log::info("Acceso a onEliminar");

        $id = post('id');
        $id = intval($id);

        $gaveta = Gaveta::find($id);

        $gaveta->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'gavetas' => $this->obtenerGavetas()
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
            'codigo' => 'required',
        ];

        $customMessages = [
            'codigo.required' => '* Campo obligatorio.'
        ];

        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    protected function datosReporte(): array
    {
        $filas = [];
        foreach (\Iehaa\Gavetas\Models\Gaveta::with('Archivero')->get() as $i => $r) {
            $filas[] = [$i + 1, 'Gaveta ' . $r->codigo, optional($r->Archivero)->codigo ? 'Archivero ' . $r->Archivero->codigo : '—'];
        }

        return ['Listado de gavetas — Fabio Castillo', ['#', 'Gaveta', 'Archivero'], $filas, 'gavetas'];
    }
}
