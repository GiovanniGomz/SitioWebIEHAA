<?php

namespace Iehaa\Facultades\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Facultades\Models\Facultad;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

use Barryvdh\DomPDF\Facade\Pdf;

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FacultadComponent extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'facultadComponent',
            'description' => 'Modulo de facultades'
        ];
    }

    public function onRun()
    {
        $this->page['facultades'] = $this->obtenerTodos();
    }

    public function obtenerTodos()
    {
        $facultades = Facultad::all();

        return $facultades;
    }

    public function onRegistrar()
    {
        \Log::info("Acceso a funcion onRegistrar");

        $data = Input::all();

        //Llave primaria
        $id = $data['id'];


        if ($id) { //Actualizando
            $this->validaciones($data);

            $facultad = Facultad::find($id);
            $facultad->nombre = $data['nombre'];

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $facultad = new Facultad();
            $facultad->nombre = $data['nombre'];

            $this->validaciones($data);

            $mensaje = '¡Almacenado correctamente!';
        }

        \Log::info("Paso validaciones");

        $facultad->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'facultades' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    function onEliminar()
    {
        $id = post('id');
        $id = intval($id);
        $facultad = Facultad::find($id);

        $facultad->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'facultades' => $this->obtenerTodos()
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
            'nombre.min'      => 'Minimo 3 caracteres'
        ];

        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    function onGetfacultad()
    {
        $id = post('id');
        $facultad = Facultad::find($id);

        return ['facultad' => $facultad];
    }

    public function generarPDF()
    {
        $facultades = $this->obtenerTodos();

        \Log::info(json_encode($facultades));

        $data = [
            'fecha' => now(),
            'facultades' => $facultades,
            'titulo' => 'Listado de tipos de facultades'
        ];

        $pdf = Pdf::loadView('iehaa.facultades::reporte', $data);

        return $pdf->download('reporte_facultades.pdf');
    }

    public function generarExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Facultades IEHAA');

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
        $sheet->setCellValue('A2', 'Reporte de Facultades');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->getStyle('A2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->setCellValue('A3', 'Fecha de generación: ' . now()->format('d/m/Y H:i:s'));

        $sheet->setCellValue('A5', '#');
        $sheet->setCellValue('B5', 'Nombre');

        $sheet->getStyle('A5:B5')->applyFromArray($headerStyle);

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(80);

        $facultades = $this->obtenerTodos();

        $fila = 6;
        $contador = 1;
        foreach ($facultades as $doc) {
            $sheet->setCellValue('A' . $fila, $contador);
            $sheet->setCellValue('B' . $fila, $doc['nombre']);

            $fila++;
            $contador++;
        }

        $ultimaFila = $fila - 1;
        $sheet->setCellValue('B' . $fila, 'TOTAL FACULTADES:');
        $sheet->setCellValue('B' . $fila, ($fila - 6) . ' facultades');
        $sheet->getStyle('B' . $fila . ':B' . $fila)->getFont()->setBold(true);

        $sheet->getStyle('A5:B' . $ultimaFila)->getBorders()->applyFromArray([
            'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
        ]);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $filename = 'Reporte_facultades_IEHAA_' . now()->format('Ymd_His') . '.xlsx';

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
