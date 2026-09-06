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

    public function generarPDF()
    {
        $estantes = Estante::all();

        \Log::info(json_encode($estantes));

        $data = [
            'fecha' => now(),
            'estantes' => $estantes,
            'titulo' => 'Listado de estantes Fondo Bibliográfico'
        ];

        $pdf = Pdf::loadView('iehaa.estante::reporte', $data);

        return $pdf->download('reporte_estante_fondo.pdf');
    }


    public function generarExcel()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Estantes IEHAA');

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
            'Reporte de Estantes'
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
        $sheet->setCellValue('B5', 'Nombre del estante');

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

        $estantes = Estante::all();

        $fila = 6;
        $contador = 1;

        foreach ($estantes as $estante) {

            // Correlativo
            $sheet->setCellValue(
                'A' . $fila,
                $contador
            );

            // Nombre
            $sheet->setCellValue(
                'B' . $fila,
                'Estante ' . $estante['codigo'] ?? 'No disponible'
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
            ($fila - 6) . ' estantes'
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
            'Reporte_estantes_' .
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
