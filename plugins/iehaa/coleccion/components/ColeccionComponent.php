<?php

namespace Iehaa\Coleccion\Components;

use Cms\Classes\ComponentBase;

use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

use Barryvdh\DomPDF\Facade\Pdf;
use Iehaa\Anaquel\Models\Anaquel;
use Iehaa\Coleccion\Models\Coleccion;

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


class ColeccionComponent extends ComponentBase
{
    use \Iehaa\Reportes\Classes\ReporteModulo;

    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'coleccionComponent',
            'description' => 'Modulo de colección'
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
        $this->page['colecciones'] = $this->obtenerColecciones();
    }

    public function obtenerColecciones()
    {
        $url = get('id');

        return Coleccion::whereHas('Anaquel', function ($query) use ($url) {
            $query->where('url', $url);
        })->get();
    }

    public function onRegistrar()
    {
        \Log::info("Acceso a onRegistar de colecciones");

        $data = Input::all();

        //Llave primaria
        $id = $data['id'];


        if ($id) { //Actualizando
            $this->validaciones($data);

            $coleccion = Coleccion::find($id);
            $coleccion->nombre = $data['nombre'];

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $url = get('id');
            $anaquel = Anaquel::where('url', $url)->first();

            if (!$anaquel) {
                exit;
            }

            $coleccion = new Coleccion();
            $coleccion->nombre = $data['nombre'];
            $coleccion->url = $this->generarURL();
            $coleccion->anaquel_id = $anaquel->id;

            \Log::info("Datos de coleccion");
            \Log::info($coleccion);

            $this->validaciones($data);

            $mensaje = '¡Almacenado correctamente!';
        }

        $coleccion->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'colecciones' => $this->obtenerColecciones()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    function onGetColeccion()
    {
        $id = post('id');
        $coleccion = Coleccion::find($id);

        return ['coleccion' => $coleccion];
    }

    function onEliminar()
    {
        \Log::info("Acceso a onEliminar");

        $id = post('id');
        $id = intval($id);

        $coleccion = Coleccion::find($id);

        $coleccion->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'colecciones' => $this->obtenerColecciones()
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
        foreach (\Iehaa\Coleccion\Models\Coleccion::with('Anaquel.Estante')->get() as $i => $r) {
            $filas[] = [$i + 1, $r->nombre, optional($r->Anaquel)->codigo ? 'Anaquel ' . $r->Anaquel->codigo : '—', optional(optional($r->Anaquel)->Estante)->codigo ? 'Estante ' . $r->Anaquel->Estante->codigo : '—'];
        }

        return ['Listado de colecciones — Fondo Bibliográfico', ['#', 'Colección', 'Anaquel', 'Estante'], $filas, 'colecciones'];
    }
}
