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

    public function generarPDF()
    {
        $folders = $this->obtenerFolders();

        $url = get('id');
        $carpeta = Carpeta::where('url', $url)->first();

        \Log::info('Acceso a generar pdf');
        \Log::info(json_encode($folders));

        $titulo = 'Listado de Folders de carpeta ' . $carpeta->nombre;

        $data = [
            'fecha' => now(),
            'folders' => $folders,
            'titulo' => $titulo
        ];

        $pdf = Pdf::loadView('iehaa.folders::reporte', $data);

        return $pdf->download('reporte_folder.pdf');
    }


    public function generarExcel()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $url = get('id');
        $carpeta = Carpeta::where('url', $url)->first();
        $titulo = 'Folders de la Carpeta ' . $carpeta->nombre . ' IEHAA';

        $sheet->setTitle('Folders IEHAA');

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

        $folders = $this->obtenerFolders();

        $fila = 6;
        $contador = 1;

        foreach ($folders as $folder) {

            // Correlativo
            $sheet->setCellValue(
                'A' . $fila,
                $contador
            );

            // Nombre
            $sheet->setCellValue(
                'B' . $fila,
                $folder['nombre'] ?? 'No disponible'
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
            ($fila - 6) . ' folders'
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
            'Reporte_folders_' .
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

    /**
     * Returns the properties provided by the component
     */
    public function defineProperties()
    {
        return [];
    }
}
