<?php

namespace Iehaa\Anaquel\Components;

use Cms\Classes\ComponentBase;

use Illuminate\Support\Facades\DB;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

use Barryvdh\DomPDF\Facade\Pdf;
use Iehaa\Anaquel\Models\Anaquel;
use Iehaa\Estante\Models\Estante;

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


class AnaquelComponent extends ComponentBase
{
    use \Iehaa\Reportes\Classes\ReporteModulo;

    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'anaquelComponent',
            'description' => 'Modulo de anaquel'
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
        $this->page['anaqueles'] = $this->obtenerAnaqueles();
    }

    public function obtenerAnaqueles()
    {
        $url = get('id');

        return Anaquel::whereHas('Estante', function ($query) use ($url) {
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

            $anaquel = Anaquel::find($id);
            $anaquel->codigo = $data['codigo'];

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $url = get('id');
            $estante = Estante::where('url', $url)->first();

            if (!$estante) {
                exit;
            }

            $anaquel = new Anaquel();
            $anaquel->codigo = $data['codigo'];
            $anaquel->url = $this->generarURL();
            $anaquel->estante_id = $estante->id;

            $this->validaciones($data);

            $mensaje = '¡Almacenado correctamente!';
        }

        $anaquel->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'anaqueles' => $this->obtenerAnaqueles()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    function onGetAnaquel()
    {
        $id = post('id');
        $anaquel = Anaquel::find($id);

        return ['anaquel' => $anaquel];
    }

    function onEliminar()
    {
        \Log::info("Acceso a onEliminar");

        $id = post('id');
        $id = intval($id);

        $anaquel = Anaquel::find($id);

        $anaquel->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'anaqueles' => $this->obtenerAnaqueles()
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
        foreach (\Iehaa\Anaquel\Models\Anaquel::with('Estante')->get() as $i => $r) {
            $filas[] = [$i + 1, 'Anaquel ' . $r->codigo, optional($r->Estante)->codigo ? 'Estante ' . $r->Estante->codigo : '—'];
        }

        return ['Listado de anaqueles — Fondo Bibliográfico', ['#', 'Anaquel', 'Estante'], $filas, 'anaqueles'];
    }
}
