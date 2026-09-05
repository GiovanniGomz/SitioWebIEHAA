<?php

namespace Iehaa\Investigadores\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Investigadores\Models\Investigador;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

use Barryvdh\DomPDF\Facade\Pdf;
use Iehaa\Categoriainvestigadores\Models\CategoriaInvestigador;
use Iehaa\Facultades\Models\Facultad;
use Iehaa\Tipoinvestigadores\Models\TipoInvestigador;

require 'vendor/autoload.php';

use Mail;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InvestigadorComponent extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'investigadorComponent',
            'description' => 'Modulo de investigadores'
        ];
    }

    public function onRun()
    {
        $this->page['investigadores'] = $this->obtenerTodos();
        $this->page['facultades'] = Facultad::all();
        $this->page['categorias'] = CategoriaInvestigador::all();
        $this->page['tipo_investigadores'] = TipoInvestigador::all();
    }

    public function obtenerTodos()
    {
        $investigadores = Investigador::with([
            'facultad',
            'tipo_investigador',
            'categoria_investigador'
        ])->get();

        return $investigadores;
    }

    public function onRegistrar()
    {
        \Log::info("Acceso a funcion onRegistrar");

        $data = Input::all();

        \Log::info($data);

        //Llave primaria
        $id = $data['id'];


        if ($id) { //Actualizando
            $this->validaciones($data);

            $investigador = Investigador::find($id);
            $investigador->nombre = $data['nombre'];
            $investigador->apellido = $data['apellido'];
            $investigador->email = $data['email'];
            $investigador->carnet = $data['carnet'];
            $investigador->telefono = $data['telefono'];
            $investigador->facultad = $data['facultad'] ?? '';
            $investigador->categoria_investigador = $data['categoria_investigador'] ?? '';
            $investigador->tipo_investigador = $data['tipo_investigador'] ?? '';
            $investigador->sexo = $data['sexo'] ?? '';
            $investigador->publicaciones = $data['publicaciones'] ?? '';
            $investigador->descripcion = $data['descripcion'] ?? '';

            $mensaje = '¡Modificado correctamente!';
        } else {
            //Creando nuevo registro

            $investigador = new Investigador();
            $investigador->nombre = $data['nombre'];
            $investigador->apellido = $data['apellido'];
            $investigador->email = $data['email'];
            $investigador->carnet = $data['carnet'];
            $investigador->telefono = $data['telefono'];
            $investigador->facultad = $data['facultad'] ?? '';
            $investigador->categoria_investigador = $data['categoria_investigador'] ?? '';
            $investigador->tipo_investigador = $data['tipo_investigador'] ?? '';
            $investigador->sexo = $data['sexo'] ?? '';
            $investigador->publicaciones = $data['publicaciones'] ?? '';
            $investigador->descripcion = $data['descripcion'] ?? '';

            $this->validaciones($data);

            $mensaje = '¡Almacenado correctamente!';
        }

        \Log::info("Paso validaciones");

        $investigador->save();

        /* if ($bandera && $id == null) {
            \Log::info("Acceso a condicion if");

            $this->sendEmail($investigador);
        }*/

        return [
            '#listado' => $this->renderPartial('@listado', [
                'investigadores' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => $mensaje
        ];
    }

    function onEliminar()
    {
        $id = post('id');
        $id = intval($id);
        $investigador = Investigador::find($id);

        $investigador->delete();

        return [
            '#listado' => $this->renderPartial('@listado', [
                'investigadores' => $this->obtenerTodos()
            ]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    public function validaciones($data)
    {
        $rules = [
            'nombre' => 'required|max:30',
            'apellido' => 'required|max:30',
            'telefono' => 'required|max:8',
            'email' => 'required|max:60',
            'carnet' => 'required|max:7',
            'facultad' => 'required',
            'categoria_investigador' => 'required',
            'tipo_investigador' => 'required',
            'sexo' => 'required',
            'descripcion' => 'required'
        ];

        $customMessages = [
            'nombre.required' => '*',
            'nombre.max'      => 'Maximo 30 caracteres',
            'apellido.required' => '*',
            'apellido.max'      => 'Maximo 30 caracteres',
            'telefono.required' => '*',
            'telefono.max'      => 'Maximo 8 caracteres',
            'email.required' => '*',
            'email.max'      => 'Maximo 60 caracteres',
            'carnet.required' => '*',
            'carnet.max'      => 'Maximo 7 caracteres',
            'facultad.required' => '*',
            'categoria_investigador.required' => '*',
            'tipo_investigador.required' => '*',
            'sexo.required' => '*',
            'descripcion.required' => '*'
        ];

        $validator = Validator::make($data, $rules, $customMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    function onGetInvestigador()
    {
        $id = post('id');
        $investigador = Investigador::find($id);

        return ['investigador' => $investigador];
    }

    public function generarPDF()
    {
        $investigadores = $this->obtenerTodos();

        \Log::info(json_encode($investigadores));

        $data = [
            'fecha' => now(),
            'investigadores' => $investigadores,
            'titulo' => 'Listado de investigadores'
        ];

        $pdf = Pdf::loadView('iehaa.investigadores::reporte', $data);

        return $pdf->download('reporte_investigadores.pdf');
    }

    public function generarExcel()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Archiveros IEHAA');

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
            'Reporte de Archiveros'
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
        $sheet->setCellValue('B5', 'Nombre del archivero');

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

        $archiveros = $this->obtenerTodos();

        $fila = 6;
        $contador = 1;

        foreach ($archiveros as $archivero) {

            // Correlativo
            $sheet->setCellValue(
                'A' . $fila,
                $contador
            );

            // Nombre
            $sheet->setCellValue(
                'B' . $fila,
                $archivero['nombre'] ?? 'No disponible'
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
            ($fila - 6) . ' archiveros'
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
            'Reporte_archiveros_' .
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



    function sendEmail(Investigador $investigador)
    {
        \Log::info("Acceso a sendEmail");

        $password = $this->generarPassword();

        $data = [
            'nombre' => $investigador->nombre . ' ' . $investigador->apellido,
            'email' => $investigador->email,
            'password' => $password,
            'loginUrl' => url('/login')
        ];

        \Log::info($password);
        \Log::info($data);

        Mail::send(
            'iehaa.investigadores::mail.credenciales',
            $data,
            function ($message) use ($investigador) {
                $message->to($investigador->email);
                $message->subject('Credenciales de acceso');
            }
        );
    }

    function generarPassword()
    {
        $password = str()->random(10);
        return $password;
    }

    /**
     * Returns the properties provided by the component
     */
    public function defineProperties()
    {
        return [];
    }
}
