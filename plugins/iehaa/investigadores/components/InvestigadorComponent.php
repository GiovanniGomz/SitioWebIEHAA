<?php

namespace Iehaa\Investigadores\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Investigadores\Models\Investigador;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Validator;

use Barryvdh\DomPDF\Facade\Pdf;

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
    }

    public function obtenerTodos()
    {
        $investigadores = Investigador::all();

        foreach ($investigadores as $investigador) {
            $investigador->facultad = $this->obtenerFacultad($investigador->facultad);
            $investigador->grado = $this->obtenerGrado($investigador->grado);
        }

        return $investigadores;
    }

    public function obtenerFacultad(int $valor)
    {
        switch ($valor) {
            case 1:
                return "Facultad de Agronomía";
                break;
            case 2:
                return "Facultad de Ciencias Económicas";
                break;
            case 3:
                return "Facultad de Ciencias y Humanidades";
                break;
            case 4:
                return "Facultad de Ciencias Naturales y Matemática";
                break;
            case 5:
                return "Facultad de Ingeniería y Arquitectura";
                break;
            case 6:
                return "Facultad de Jurisprudencia y Ciencias Sociales";
                break;
            case 7:
                return "Facultad de Medicina";
                break;
            case 8:
                return "Facultad de Odontología";
                break;
            case 9:
                return "Facultad de Química y Farmacia";
                break;
            case 10:
                return "Facultad Multidisciplinaria de Occidente";
                break;
            case 11:
                return "Facultad Multidisciplinaria de Oriente";
                break;
            case 12:
                return "Facultad Multidisciplinaria Paracentral";
                break;
            default:
                return "Error al obtener facultad";
        }
    }

    public function obtenerGrado(int $valor)
    {
        switch ($valor) {
            case 1:
                return "Estudiante";
                break;
            case 2:
                return "Educación superior";
                break;
            case 3:
                return "Maestria";
                break;
            case 4:
                return "Doctorado";
                break;
            default:
                return "error";
        }
    }

    public function onRegistrar()
    {
        \Log::info("Acceso a funcion onRegistrar");

        $data = Input::all();

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
            $investigador->grado = $data['grado'] ?? '';

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
            $investigador->grado = $data['grado'] ?? '';

            $this->validaciones($data);

            $mensaje = '¡Almacenado correctamente!';
        }

        \Log::info("Paso validaciones");

        $bandera = $investigador->save();

        if ($bandera && $id == null) {
            \Log::info("Acceso a condicion if");

            $this->sendEmail($investigador);
        }

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
            'grado' => 'required'
        ];

        $customMessages = [
            'nombre.required' => '* Campo obligatorio.',
            'nombre.max'      => 'Maximo 30 caracteres',
            'apellido.required' => '* Campo obligatorio.',
            'apellido.max'      => 'Maximo 30 caracteres',
            'telefono.required' => '* Campo obligatorio.',
            'telefono.max'      => 'Maximo 8 caracteres',
            'email.required' => '* Campo obligatorio.',
            'email.max'      => 'Maximo 60 caracteres',
            'carnet.required' => '* Campo obligatorio.',
            'carnet.max'      => 'Maximo 7 caracteres',
            'facultad.required' => '* Campo obligatorio.',
            'grado.required' => '* Campo obligatorio.'
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
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Investigadores IEHAA');

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

        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'INSTITUTO DE ESTUDIOS HISTÓRICOS, ANTROPOLÓGICOS Y ARQUEOLÓGICOS');
        $sheet->getStyle('A1')->applyFromArray($titleStyle);

        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', 'Reporte de Investigadores');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);

        $sheet->getStyle('A2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->setCellValue('A3', 'Fecha de generación: ' . now()->format('d/m/Y H:i:s'));

        $sheet->setCellValue('A5', '#');
        $sheet->setCellValue('B5', 'Nombre completo');
        $sheet->setCellValue('C5', 'Carnet');
        $sheet->setCellValue('D5', 'Facultad');
        $sheet->setCellValue('E5', 'Grado academico');

        $sheet->getStyle('A5:E5')->applyFromArray($headerStyle);

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(80);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(80);
        $sheet->getColumnDimension('E')->setWidth(20);

        $investigadores = $this->obtenerTodos();

        $fila = 6;
        $contador = 1;
        foreach ($investigadores as $doc) {
            $sheet->setCellValue('A' . $fila, $contador);
            $sheet->setCellValue('B' . $fila, $doc['nombre'] . ' ' . $doc['apellido']);
            $sheet->setCellValue('C' . $fila, $doc['carnet']);
            $sheet->setCellValue('D' . $fila, $doc['facultad']);
            $sheet->setCellValue('E' . $fila, $doc['grado']);

            //$sheet->getStyle('D' . $fila)->getAlignment()->setHorizontal('right');

            $fila++;
            $contador++;
        }

        $ultimaFila = $fila - 1;
        $sheet->setCellValue('B' . $fila, 'TOTAL INVESTIGADORES:');
        $sheet->setCellValue('E' . $fila, ($fila - 6) . ' investigadores');
        $sheet->getStyle('B' . $fila . ':E' . $fila)->getFont()->setBold(true);

        $sheet->getStyle('A5:E' . $ultimaFila)->getBorders()->applyFromArray([
            'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
        ]);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $filename = 'Reporte_investigadores' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
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
