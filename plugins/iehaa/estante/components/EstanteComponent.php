<?php

namespace Iehaa\Estante\Components;

use Cms\Classes\ComponentBase;

use Illuminate\Support\Facades\DB;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

use Barryvdh\DomPDF\Facade\Pdf;
use Iehaa\Estante\Models\Estante;

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class EstanteComponent extends ComponentBase
{
    use \Iehaa\Reportes\Classes\ReporteModulo;

    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'estanteComponent',
            'description' => 'Modulo de estante'
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
        $this->page['estantes'] = Estante::all();
    }

    public function onRegistrar()
    {
        $data = Input::all();

        //Llave primaria
        $id = $data['id'];


        if ($id) { //Actualizando
            $this->validaciones($data);

            $estante = Estante::find($id);
            $estante->codigo = $data['codigo'];

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $estante = new Estante();
            $estante->codigo = $data['codigo'];
            $estante->url = $this->generarURL();

            $this->validaciones($data);

            $mensaje = '¡Almacenado correctamente!';
        }

        $estante->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'estantes' => Estante::all()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    function onGetEstante()
    {
        $id = post('id');
        $estante = Estante::find($id);

        return ['estante' => $estante];
    }

    function onEliminar()
    {
        \Log::info("Acceso a onEliminar");

        $id = post('id');
        $id = intval($id);

        $estante = Estante::find($id);

        $estante->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'estantes' => Estante::all()
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
            'codigo.required' => '* Campo obligatorio.',
        ];

        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    protected function datosReporte(): array
    {
        $filas = [];
        foreach (\Iehaa\Estante\Models\Estante::get() as $i => $r) {
            $filas[] = [$i + 1, 'Estante ' . $r->codigo];
        }

        return ['Listado de estantes — Fondo Bibliográfico', ['#', 'Estante'], $filas, 'estantes'];
    }
}
