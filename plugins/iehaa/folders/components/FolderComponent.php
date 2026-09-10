<?php

namespace Iehaa\Folders\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Carpetas\Models\Carpeta;
use Iehaa\Folders\Models\Folder;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

use Barryvdh\DomPDF\Facade\Pdf;

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FolderComponent extends ComponentBase
{
    use \Iehaa\Reportes\Classes\ReporteModulo;

    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'folderComponent',
            'description' => 'Modulo de folder'
        ];
    }

    public function onRun()
    {
        $this->page['folders'] = $this->obtenerFolders();
    }

    public function obtenerFolders()
    {
        $url = get('id');

        return Folder::whereHas('Carpeta', function ($query) use ($url) {
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

            $folder = Folder::find($id);
            $folder->nombre = $data['nombre'];

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $url = get('id');
            $carpeta = Carpeta::where('url', $url)->first();

            if (!$carpeta) {
                exit;
            }

            $folder = new Folder();
            $folder->nombre = $data['nombre'];
            $folder->url = $this->generarURL();
            $folder->carpeta_id = $carpeta->id;

            \Log::info("Datos de folder");
            \Log::info($folder);

            $this->validaciones($data);

            $mensaje = '¡Almacenado correctamente!';
        }

        $folder->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'folders' => $this->obtenerFolders()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    function onGetFolder()
    {
        $id = post('id');
        $folder = Folder::find($id);

        return ['folder' => $folder];
    }

    function onEliminar()
    {
        \Log::info("Acceso a onEliminar");

        $id = post('id');
        $id = intval($id);

        $folder = Folder::find($id);

        $folder->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'folders' => $this->obtenerFolders()
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
        foreach (\Iehaa\Folders\Models\Folder::with('Carpeta.Gaveta')->get() as $i => $r) {
            $filas[] = [$i + 1, $r->nombre, optional($r->Carpeta)->nombre ?: '—', optional(optional($r->Carpeta)->Gaveta)->codigo ? 'Gaveta ' . $r->Carpeta->Gaveta->codigo : '—'];
        }

        return ['Listado de folders — Fabio Castillo', ['#', 'Folder', 'Carpeta', 'Gaveta'], $filas, 'folders'];
    }
}
