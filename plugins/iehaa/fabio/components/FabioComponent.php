<?php

namespace Iehaa\Fabio\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Carpetas\Models\Carpeta;
use Iehaa\Fabio\Models\Fabio;
use Iehaa\Folders\Models\Folder;
use Illuminate\Support\Facades\Storage;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

use Barryvdh\DomPDF\Facade\Pdf;

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FabioComponent extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'fabioComponent',
            'description' => 'Modulo de archivos Fabio Castillo'
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

        \Log::info('Esta es la url de fabio');
        \Log::info($url);

        $documentos = Fabio::whereHas('Folder', function ($query) use ($url) {
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

            $fabio = Fabio::find($id);

            if ($archivo) {
                $fabio->archivo = $this->guardarArchivo($archivo, $fabio->archivo);
            }

            $fabio->nombre = $data['nombre'];

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $url = get('id');

            \Log::info("URL al almacenar");
            \Log::info($url);

            $folder = Folder::where('url', $url)->first();

            \Log::info($folder);

            if (!$folder) {
                exit;
            }

            $fabio = new Fabio();
            $fabio->nombre = $data['nombre'];
            $fabio->folder_id = $folder->id;

            $this->validacionesRegistrar($data);

            $fabio->archivo = $this->guardarArchivo($archivo);

            $mensaje = '¡Almacenado correctamente!';
        }

        $fabio->save();

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
        $fabio = Fabio::find($id);

        return ['documento' => $fabio];
    }

    function onEliminar()
    {
        $id = post('id');
        $id = intval($id);
        $fabio = Fabio::find($id);

        $this->eliminarArchivo($fabio->archivo);

        $fabio->delete();

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
        $uploadPath = 'storage/app/uploads/public/fabio/';

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
        Storage::delete('uploads/public/fabio/' . $nombreArchivo);
    }

    public function generarPDF()
    {
        $documentos = $this->obtenerDocumentos();

        $url = get('id');
        $folder = Folder::where('url', $url)->first();

        \Log::info('Acceso a generar pdf');
        \Log::info(json_encode($documentos));

        $titulo = 'Listado de documentos de folder ' . $folder->nombre;

        $data = [
            'fecha' => now(),
            'documentos' => $documentos,
            'titulo' => $titulo
        ];

        $pdf = Pdf::loadView('iehaa.fabio::reporte', $data);

        return $pdf->download('reporte_fabio.pdf');
    }


    public function generarExcel()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $url = get('id');
        $folder = Folder::where('url', $url)->first();
        $titulo = 'Documentos del folder ' . $folder->nombre . ' IEHAA';

        $sheet->setTitle('Documentos fabio IEHAA');

        // =========================
        // ESTILO DEL ENCABEZADO
        // =========================

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],

            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E79']
            ],

            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],

            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ],
        ];

        // =========================
        // ESTILO DEL TÍTULO
        // =========================

        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 16
            ],

            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
        ];

        // =========================
        // TÍTULO PRINCIPAL
        // =========================

        $sheet->mergeCells('A1:B1');

        $sheet->setCellValue(
            'A1',
            'INSTITUTO DE ESTUDIOS HISTÓRICOS, ANTROPOLÓGICOS Y ARQUEOLÓGICOS'
        );

        $sheet->getStyle('A1')->applyFromArray($titleStyle);

        // =========================
        // SUBTÍTULO
        // =========================

        $sheet->mergeCells('A2:B2');

        $sheet->setCellValue(
            'A2',
            $titulo
        );

        $sheet->getStyle('A2')->getFont()
            ->setBold(true)
            ->setSize(14);

        $sheet->getStyle('A2')->getAlignment()
            ->setHorizontal(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            )
            ->setVertical(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            );

        // =========================
        // FECHA
        // =========================

        $sheet->setCellValue(
            'A3',
            'Fecha de generación: ' . now()->format('d/m/Y H:i:s')
        );

        // =========================
        // ENCABEZADOS DE TABLA
        // =========================

        $sheet->setCellValue('A5', '#');
        $sheet->setCellValue('B5', 'Folder');

        $sheet->getStyle('A5:B5')
            ->applyFromArray($headerStyle);

        // =========================
        // ANCHO DE COLUMNAS
        // =========================

        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(50);

        // =========================
        // OBTENER ARCHIVEROS
        // =========================

        $documentos = $this->obtenerDocumentos();

        $fila = 6;
        $contador = 1;

        foreach ($documentos as $documento) {

            // Correlativo
            $sheet->setCellValue(
                'A' . $fila,
                $contador
            );

            // Nombre
            $sheet->setCellValue(
                'B' . $fila,
                $documento['nombre'] ?? 'No disponible'
            );

            $fila++;
            $contador++;
        }

        // =========================
        // TOTAL
        // =========================

        $ultimaFila = $fila - 1;

        $sheet->mergeCells(
            'A' . $fila . ':A' . $fila
        );

        $sheet->setCellValue(
            'A' . $fila,
            'TOTAL'
        );

        $sheet->setCellValue(
            'B' . $fila,
            ($fila - 6) . ' documentos fabio'
        );

        $sheet->getStyle(
            'A' . $fila . ':B' . $fila
        )->getFont()->setBold(true);

        // =========================
        // BORDES DE LA TABLA
        // =========================

        $sheet->getStyle(
            'A5:B' . $ultimaFila
        )->getBorders()->applyFromArray([
            'allBorders' => [
                'borderStyle' =>
                \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            ]
        ]);

        // Bordes de TOTAL

        $sheet->getStyle(
            'A' . $fila . ':B' . $fila
        )->getBorders()->applyFromArray([
            'allBorders' => [
                'borderStyle' =>
                \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            ]
        ]);

        // =========================
        // ALINEACIÓN
        // =========================

        $sheet->getStyle(
            'A5:B' . $fila
        )->getAlignment()
            ->setVertical(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            );

        // Correlativo centrado

        $sheet->getStyle(
            'A5:A' . $ultimaFila
        )->getAlignment()
            ->setHorizontal(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            );

        // =========================
        // AJUSTAR TEXTO
        // =========================

        $sheet->getStyle(
            'A5:B' . $ultimaFila
        )->getAlignment()
            ->setWrapText(true);

        // =========================
        // ALTURA DEL ENCABEZADO
        // =========================

        $sheet->getRowDimension(5)->setRowHeight(30);

        // =========================
        // CONGELAR ENCABEZADO
        // =========================

        $sheet->freezePane('A6');

        // =========================
        // GENERAR ARCHIVO
        // =========================

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx(
            $spreadsheet
        );

        $filename =
            'Reporte_fabio_' .
            now()->format('Ymd_His') .
            '.xlsx';

        return response()->streamDownload(
            function () use ($writer) {
                $writer->save('php://output');
            },
            $filename,
            [
                'Content-Type' =>
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }
}
