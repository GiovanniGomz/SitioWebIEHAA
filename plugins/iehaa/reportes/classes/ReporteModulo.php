<?php

namespace Iehaa\Reportes\Classes;

/**
 * Da a cualquier componente de módulo los métodos generarPdf() / generarExcel()
 * usando el GeneradorReportes común. El componente solo define datosReporte().
 *
 *   use Iehaa\Reportes\Classes\ReporteModulo;
 *
 *   class FacultadComponent extends ComponentBase
 *   {
 *       use ReporteModulo;
 *
 *       protected function datosReporte(): array
 *       {
 *           $filas = Facultad::orderBy('nombre')->get()->values()->map(fn ($f, $i) => [
 *               $i + 1, $f->nombre,
 *           ])->all();
 *
 *           return ['Listado de facultades', ['#', 'Nombre'], $filas, 'facultades'];
 *       }
 *   }
 */
trait ReporteModulo
{
    /**
     * @return array{0:string,1:array,2:array,3:string}  [titulo, columnas, filas, nombreArchivo]
     */
    abstract protected function datosReporte(): array;

    public function generarPdf()
    {
        [$titulo, $columnas, $filas, $nombre] = array_pad($this->datosReporte(), 4, null);

        $orientacion = count($columnas) > 5 ? 'landscape' : 'portrait';

        return GeneradorReportes::pdf($titulo, $columnas, $filas, $nombre, $orientacion);
    }

    public function generarExcel()
    {
        [$titulo, $columnas, $filas, $nombre] = array_pad($this->datosReporte(), 4, null);

        return GeneradorReportes::excel($titulo, $columnas, $filas, $nombre);
    }
}
