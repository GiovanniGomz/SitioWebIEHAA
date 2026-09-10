<?php

namespace Iehaa\Reportes\Classes;

use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Generador único de reportes PDF y Excel para todos los módulos del panel.
 *
 * Cada módulo solo tiene que armar sus columnas y filas y llamar aquí, así
 * todos los reportes salen con el mismo encabezado institucional y formato.
 *
 *   use Iehaa\Reportes\Classes\GeneradorReportes;
 *
 *   return GeneradorReportes::pdf(
 *       'Listado de investigadores',
 *       ['#', 'Nombre', 'Carnet', 'Facultad'],
 *       $filas,          // [[1, 'Juan Pérez', 'JP21001', 'Ingeniería'], ...]
 *       'investigadores'
 *   );
 */
class GeneradorReportes
{
    public const INSTITUTO = 'Instituto de Estudios Históricos, Antropológicos y Arqueológicos';
    public const UNIVERSIDAD = 'Universidad de El Salvador';

    /**
     * Devuelve la respuesta de descarga de un PDF con el listado indicado.
     */
    public static function pdf(string $titulo, array $columnas, array $filas, ?string $nombreArchivo = null, string $orientacion = 'portrait')
    {
        $filas = array_map('array_values', $filas);

        $pdf = Pdf::loadView('iehaa.reportes::reporte-generico', [
            'titulo'    => $titulo,
            'columnas'  => $columnas,
            'filas'     => $filas,
            'total'     => count($filas),
            'generado'  => now()->format('d/m/Y H:i'),
            'logo'      => self::logoBase64(),
        ]);

        $pdf->setPaper('a4', $orientacion);

        return $pdf->download(self::nombre($nombreArchivo ?? $titulo, 'pdf'));
    }

    /**
     * Devuelve la respuesta de descarga de un Excel (.xlsx) con el listado.
     */
    public static function excel(string $titulo, array $columnas, array $filas, ?string $nombreArchivo = null)
    {
        $filas = array_map('array_values', $filas);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte');

        $ultimaColumna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(max(1, count($columnas)));

        // Encabezado institucional
        $sheet->mergeCells("A1:{$ultimaColumna}1");
        $sheet->setCellValue('A1', strtoupper(self::INSTITUTO));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A2:{$ultimaColumna}2");
        $sheet->setCellValue('A2', $titulo);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A3:{$ultimaColumna}3");
        $sheet->setCellValue('A3', 'Generado el ' . now()->format('d/m/Y H:i'));
        $sheet->getStyle('A3')->getFont()->setSize(9)->getColor()->setRGB('666666');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Encabezados de tabla (fila 5)
        $filaEncabezado = 5;
        foreach ($columnas as $i => $columna) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . $filaEncabezado, $columna);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle("A{$filaEncabezado}:{$ultimaColumna}{$filaEncabezado}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B3261E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($filaEncabezado)->setRowHeight(22);

        // Datos
        $fila = $filaEncabezado + 1;
        foreach ($filas as $registro) {
            foreach (array_values($registro) as $i => $valor) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValueExplicit(
                    $col . $fila,
                    self::texto($valor),
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
            }
            $fila++;
        }

        $ultimaFila = max($filaEncabezado, $fila - 1);

        $sheet->getStyle("A{$filaEncabezado}:{$ultimaColumna}{$ultimaFila}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->setCellValue("A{$fila}", 'Total de registros: ' . count($filas));
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true);

        $sheet->freezePane('A' . ($filaEncabezado + 1));

        $writer = new Xlsx($spreadsheet);
        $nombre = self::nombre($nombreArchivo ?? $titulo, 'xlsx');

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $nombre, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    protected static function texto($valor): string
    {
        if ($valor === null || $valor === false) {
            return '';
        }

        if (is_bool($valor)) {
            return $valor ? 'Sí' : 'No';
        }

        return (string) $valor;
    }

    protected static function nombre(string $base, string $extension): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', \Str::ascii($base)), '_'));
        $slug = $slug !== '' ? $slug : 'reporte';

        return $slug . '_' . now()->format('Ymd_His') . '.' . $extension;
    }

    protected static function logoBase64(): ?string
    {
        $ruta = base_path('plugins/iehaa/reportes/assets/logo-ues.png');

        if (!is_file($ruta)) {
            return null;
        }

        return 'data:image/png;base64,' . base64_encode(file_get_contents($ruta));
    }
}
