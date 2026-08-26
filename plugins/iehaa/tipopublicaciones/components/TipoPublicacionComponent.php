<?php

namespace Iehaa\Tipopublicaciones\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Tipopublicaciones\Models\TipoPublicaciones;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

use Barryvdh\DomPDF\Facade\Pdf;

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TipoPublicacionComponent extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'TipoPublicacionComponent',
            'description' => 'Modulo de tipo de publicaciones'
        ];
    }

    public function onRun()
    {
        $this->page['tipo_publicaciones'] = $this->obtenerTodos();
    }

    public function obtenerTodos()
    {
        $tipo_publicaciones = TipoPublicaciones::all();

        return $tipo_publicaciones;
    }

    public function onRegistrar()
    {
        \Log::info("Acceso a funcion onRegistrar");

        $data = Input::all();

        //Llave primaria
        $id = $data['id'];


        if ($id) { //Actualizando
            $this->validaciones($data);

            $tipo_publicaciones = TipoPublicaciones::find($id);
            $tipo_publicaciones->nombre = $data['nombre'];

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $tipo_publicaciones = new TipoPublicaciones();
            $tipo_publicaciones->nombre = $data['nombre'];

            $this->validaciones($data);

            $mensaje = '¡Almacenado correctamente!';
        }

        \Log::info("Paso validaciones");

        $tipo_publicaciones->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'tipo_publicaciones' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    function onEliminar()
    {
        $id = post('id');
        $id = intval($id);
        $tipo_publicaciones = TipoPublicaciones::find($id);

        $tipo_publicaciones->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'tipo_publicaciones' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function validaciones($data)
    {
        $rules = [
            'nombre' => 'required|min:3',
        ];

        $customMessages = [
            'nombre.required' => '* Campo obligatorio.',
            'nombre.max'      => 'Minimo 3 caracteres'
        ];

        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    function onGetTipoPublicacion()
    {
        $id = post('id');
        $tipo_publicaciones = TipoPublicaciones::find($id);

        return ['tipo_publicacion' => $tipo_publicaciones];
    }

    public function generarPDF()
    {
        $tipoPublicaciones = $this->obtenerTodos();

        \Log::info(json_encode($tipoPublicaciones));

        $data = [
            'fecha' => now(),
            'tipos' => $tipoPublicaciones,
            'titulo' => 'Listado de tipos de publicaciones'
        ];

        $pdf = Pdf::loadView('iehaa.tipopublicaciones::reporte', $data);

        return $pdf->download('reporte_tipo_publicaciones.pdf');
    }

    public function generarExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Tipos de publicaciones IEHAA');

        // Estilos
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];

        $titleStyle = [
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];

        $sheet->mergeCells('A1:B1');
        $sheet->setCellValue('A1', 'INSTITUTO DE ESTUDIOS HISTÓRICOS, ANTROPOLÓGICOS Y ARQUEOLÓGICOS');
        $sheet->getStyle('A1')->applyFromArray($titleStyle);

        $sheet->mergeCells('A2:B2');
        $sheet->setCellValue('A2', 'Reporte de Tipo de publicaciones');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->getStyle('A2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->setCellValue('A3', 'Fecha de generación: ' . now()->format('d/m/Y H:i:s'));

        $sheet->setCellValue('A5', '#');
        $sheet->setCellValue('B5', 'Tipo de publicación');

        $sheet->getStyle('A5:B5')->applyFromArray($headerStyle);

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(80);

        $tipoPublicaciones = $this->obtenerTodos();

        $fila = 6;
        foreach ($tipoPublicaciones as $doc) {
            $sheet->setCellValue('A' . $fila, $doc['id']);
            $sheet->setCellValue('B' . $fila, $doc['nombre']);

            $fila++;
        }

        $ultimaFila = $fila - 1;
        $sheet->setCellValue('B' . $fila, 'TOTAL TIPOS DE PUBLICACIONES:');
        $sheet->setCellValue('B' . $fila, ($fila - 6) . ' tipo de publicaciones');
        $sheet->getStyle('B' . $fila . ':B' . $fila)->getFont()->setBold(true);

        $sheet->getStyle('A5:B' . $ultimaFila)->getBorders()->applyFromArray([
            'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
        ]);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $filename = 'Reporte_tipo_publicaciones_IEHAA_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Returns the properties provided by the component
     */
    public function defineProperties()
    {
        return [];
    }
}
