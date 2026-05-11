<?php

namespace IEHAA\Documentos\Components;

use Cms\Classes\ComponentBase;
use IEHAA\Documentos\Models\Documento;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

use Illuminate\Support\Facades\Storage;

use Barryvdh\DomPDF\Facade\Pdf;

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DocumentoComponent extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'documentoComponent',
            'description' => 'Modulo de documentos'
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
        $this->page['documentos'] = Documento::all();
    }

    public function onRegistrar()
    {
        $data = Input::all();
        $archivo = Input::file('archivo');

        //Llave primaria
        $id = $data['id'];


        if ($id) { //Actualizando
            $this->validacionesModificar($data, $archivo);

            $documento = Documento::find($id);
            $documento->nombre = $data['nombre'];

            if ($archivo) {
                $documento->peso = $this->calcularPeso($archivo);
                $documento->archivo = $this->guardarArchivo($archivo, $data['archivo_tmp']);
            } else {
                //Si no viene archivo es porque no se esta cambiando
                $documento->archivo = $data['archivo_tmp'];
            }

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $documento = new Documento();
            $documento->nombre = $data['nombre'];

            $this->validacionesRegistrar($data);

            $documento->peso = $this->calcularPeso($archivo);
            $documento->archivo = $this->guardarArchivo($archivo);

            $mensaje = '¡Almacenado correctamente!';
        }

        $documento->save();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'documentos' => Documento::all()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    function onGetDocumento()
    {
        $id = post('id');
        $documento = Documento::find($id);

        return ['documento' => $documento];
    }

    function onEliminar()
    {
        $id = post('id');
        $id = intval($id);
        $documento = Documento::find($id);

        $this->eliminarArchivo($documento->archivo);

        $documento->delete();

        \Log::info(Documento::all());

        return [
            '#listado' => $this->renderPartial('@listado', [
                'documentos' => Documento::all()
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

    public function validacionesModificar($data, $archivo)
    {
        if (!$archivo && !$data['archivo_tmp']) {

            $rules = [
                'nombre' => 'required|min:3',
                'archivo' => 'required'
            ];

            $customMessages = [
                'nombre.required' => '* Campo obligatorio.',
                'nombre.min'      => 'Minimo 3 caracteres',
                'archivo.required' => '* Campo obligatorio'
            ];
        } else {
            $rules = [
                'nombre' => 'required|min:3',
            ];

            $customMessages = [
                'nombre.required' => '* Campo obligatorio.',
                'nombre.min'      => 'Minimo 3 caracteres',
            ];
        }



        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function guardarArchivo($archivo, $nombreArchivo = false)
    {
        $uploadPath = 'storage/app/uploads/public/documentos/';

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
        Storage::delete('uploads/public/documentos/' . $nombreArchivo);
    }

    public function generarPDF()
    {
        \Log::info(json_encode(Documento::all()));

        $data = [
            'fecha' => now(),
            'archivos' => Documento::all(),
            'titulo' => 'Listado de documentos'
        ];

        $pdf = Pdf::loadView('iehaa.documentos::reporte', $data);

        return $pdf->download('reporte_documentos.pdf');
    }

    public function generarExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Documentos IEHAA');

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

        $sheet->mergeCells('A1:C1');
        $sheet->setCellValue('A1', 'INSTITUTO DE ESTUDIOS HISTÓRICOS, ANTROPOLÓGICOS Y ARQUEOLÓGICOS');
        $sheet->getStyle('A1')->applyFromArray($titleStyle);

        $sheet->mergeCells('A2:C2');
        $sheet->setCellValue('A2', 'Reporte de Descargas');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->getStyle('A2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->setCellValue('A3', 'Fecha de generación: ' . now()->format('d/m/Y H:i:s'));

        $sheet->setCellValue('A5', '#');
        $sheet->setCellValue('B5', 'Nombre del Documento');
        $sheet->setCellValue('C5', 'Peso');

        $sheet->getStyle('A5:C5')->applyFromArray($headerStyle);

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(80);
        $sheet->getColumnDimension('C')->setWidth(20);

        $documentos = Documento::all();

        $fila = 6;
        foreach ($documentos as $doc) {
            $sheet->setCellValue('A' . $fila, $doc['id']);
            $sheet->setCellValue('B' . $fila, $doc['nombre']);
            $sheet->setCellValue('C' . $fila, $doc['peso']);

            $sheet->getStyle('C' . $fila)->getAlignment()->setHorizontal('right');

            $fila++;
        }

        $ultimaFila = $fila - 1;
        $sheet->setCellValue('B' . $fila, 'TOTAL DOCUMENTOS:');
        $sheet->setCellValue('C' . $fila, ($fila - 6) . ' documentos');
        $sheet->getStyle('B' . $fila . ':C' . $fila)->getFont()->setBold(true);

        $sheet->getStyle('A5:C' . $ultimaFila)->getBorders()->applyFromArray([
            'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
        ]);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $filename = 'Reporte_Documentos_IEHAA_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function calcularPeso($documento)
    {
        $sizeDocumento = $documento->getSize();

        $peso = $this->formatearDocumento($sizeDocumento);

        return $peso;
    }

    public function formatearDocumento($bytes, $decimales = 2)
    {
        $size = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimales}f", $bytes / pow(1024, $factor)) . ' ' . $size[$factor];
    }
}
